<?php
/**
 * LinkDump Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_Feeds extends Jaws_Gadget_HTML
{
    /**
     * Displays or writes a RDF feed for the link group
     *
     * @access  public
     * @return  string  xml with RDF feed on display mode, nothing otherwise
     */
    function RSS()
    {
        header('Content-type: application/rss+xml');

        $request =& Jaws_Request::getInstance();
        $gid = $request->get('id', 'get');

        $rss_path = JAWS_DATA . 'xml/link-' . $gid . '.rss';
        if (file_exists($rss_path)) {
            ///FIXME we need to do more error checking over here
            $rss = @file_get_contents($rss_path);
            return $rss;
        }

        $rss = $this->GenerateFeed($gid);
        if (Jaws_Error::IsError($rss)) {
            return '';
        }

        ///FIXME we need to do more error checking over here
        @file_put_contents($rss_path, $rss);
        Jaws_Utils::chmod($rss_path);

        return $rss;
    }

    /**
     * Generating RDF feed
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   XHTML template content or false on error
     */
    function GenerateFeed($gid)
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Groups');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false;
        }
        $links = $model->GetGroupLinks($group['id'], $group['limit_count']);
        if (Jaws_Error::IsError($links)) {
            return false;
        }

        $url    = $GLOBALS['app']->GetSiteURL('/');
        $title  = $this->gadget->registry->fetch('site_name', 'Settings');
        $desc   = $this->gadget->registry->fetch('site_description', 'Settings');
        $author = $this->gadget->registry->fetch('site_author', 'Settings');

        $tpl = $this->gadget->loadTemplate('Rdf.html', 'index');
        $tpl->SetBlock('RDF');
        $tpl->SetVariable('link', $url);
        $tpl->SetVariable('title', $title);
        $tpl->SetVariable('desc', $desc);

        foreach ($links as $link) {
            $tpl->SetBlock('RDF/RdfSeq');
            $tpl->SetVariable('rdf-seq-url', $link['url']);
            $tpl->ParseBlock('RDF/RdfSeq');
        }

        foreach ($links as $link) {
            $tpl->SetBlock('RDF/item');
            $tpl->SetVariable('item-link',      $link['url']);
            $tpl->SetVariable('item-title',     $link['title']);
            $tpl->SetVariable('item-creator',   $author);
            $tpl->SetVariable('item-date',      $link['updatetime']);
            $tpl->ParseBlock('RDF/item');
        }

        $tpl->ParseBlock('RDF');
        return $tpl->Get();
    }
}