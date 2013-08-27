<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Feeds extends Blog_HTML
{
    /**
     * Displays or writes a RSS feed for the blog
     *
     * @access  public
     * @param   bool    $save   true to save RSS, false to display
     * @return  string  xml with RSS feed on display mode, nothing otherwise
     */
    function RSS($save = false)
    {
        header('Content-type: application/rss+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model', 'Feeds');
        $rss = $model->MakeRSS($save);
        if (Jaws_Error::IsError($rss) && !$save) {
            return '';
        }

        return $rss;
    }

    /**
     * Displays or writes an Atom feed for the blog
     *
     * @access  public
     * @param   bool    $save   true to save Atom, false to display
     * @return  string  xml with Atom feed on display mode, nothing otherwise
     */
    function Atom($save = false)
    {
        header('Content-type: application/atom+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model', 'Feeds');
        $atom = $model->MakeAtom($save);
        if (Jaws_Error::IsError($atom) && !$save) {
            return '';
        }

        return $atom;
    }

    /**
     * Displays a RSS feed for a given blog category
     *
     * @access  public
     * @return  string  xml with RSS feed
     */
    function ShowRSSCategory()
    {
        header('Content-type: application/rss+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model', 'Feeds');

        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');
        $id = Jaws_XSS::defilter($id, true);

        $xml = $model->MakeCategoryRSS($id);
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays an Atom feed for a given blog category
     *
     * @access  public
     * @return  string  xml with Atom feed
     */
    function ShowAtomCategory()
    {
        header('Content-type: application/atom+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model', 'Feeds');

        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');
        $id = Jaws_XSS::defilter($id, true);

        $xml = $model->MakeCategoryAtom($id);
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays an Atom feed for blog most recent comments
     *
     * @access  public
     * @return  string  xml with Atom feed
     */
    function RecentCommentsAtom()
    {
        header('Content-type: application/atom+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model', 'Feeds');
        $xml = $model->GetRecentCommentsAtom();
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays a RSS feed for blog most recent comments
     *
     * @access  public
     * @return  string  xml with RSS feed
     */
    function RecentCommentsRSS()
    {
        header('Content-type: application/rss+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model', 'Feeds');
        $xml = $model->GetRecentCommentsRSS();
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays an Atom feed for most recent comments on the given blog entry
     *
     * @access  public
     * @return  string  xml with Atom feed
     */
    function CommentsAtom()
    {
        header('Content-type: application/atom+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model', 'Feeds');

        $request =& Jaws_Request::getInstance();
        $id = (int)$request->get('id', 'get');

        $xml = $model->GetPostCommentsAtom($id);
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays a RSS feed for most recent comments on the given blog entry
     *
     * @access  public
     * @return  string  xml with RSS feed
     */
    function CommentsRSS()
    {
        header('Content-type: application/rss+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model', 'Feeds');

        $request =& Jaws_Request::getInstance();
        $id = (int)$request->get('id', 'get');

        $xml = $model->GetPostCommentsRSS($id);
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }


    /**
     * Get then FeedsLink action params
     *
     * @access  public
     * @return  array list of the Banners action params
     */
    function FeedsLinkLayoutParams()
    {
        $result = array();
        $result[] = array(
            'title' => _t('FEEDS_CONTENT_TYPE'),
            'value' => array(
                'entry' => _t('FEEDS_CONTENT_ENTRY'),
                'comment' => _t('FEEDS_CONTENT_COMMENT'),
            )
        );

        $result[] = array(
            'title' => _t('FEEDS_LINK_TYPE'),
            'value' => array(
                'RSS' => _t('RSSLINK') ,
                'Atom' => _t('ATOMLINK') ,
            )
        );
        return $result;
    }

    /**
     * Displays a link to blog feed
     *
     * @access  public
     * @param   string  $contentType (entry | comment)
     * @param   string  $linkType (RSS | Atom)
     * @return  string  XHTML template content
     */
    function FeedsLink($contentType, $linkType)
    {
        $tpl = $this->gadget->loadTemplate('XMLLinks.html');
        if ($contentType == 'comment') {
            if ($linkType == 'RSS') {
                $tpl->SetBlock('recentcomments_rss_link');
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'RecentCommentsRSS'));
                $tpl->ParseBlock('recentcomments_rss_link');
            } else if ($linkType == 'Atom') {
                $tpl->SetBlock('recentcomments_atom_link');
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'RecentCommentsAtom'));
                $tpl->ParseBlock('recentcomments_atom_link');
            }
        } else if($contentType=='entry'){
            if ($linkType == 'RSS') {
                $tpl->SetBlock('rss_link');
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'RSS'));
                $tpl->ParseBlock('rss_link');
            } else if ($linkType == 'Atom') {
                $tpl->SetBlock('atom_link');
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'Atom'));
                $tpl->ParseBlock('atom_link');
            }
        }
        return $tpl->Get();
    }

}