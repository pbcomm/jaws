<?php
/**
 * Blog - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $items = array();
        $items[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Blog', 'DefaultAction'),
                         'title'  => _t('BLOG_NAME'));
        $items[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Blog', 'Archive'),
                         'title'  => _t('BLOG_ARCHIVE'));
        $items[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Blog', 'CategoriesList'),
                         'title'  => _t('BLOG_ACTIONS_CATEGORIESLIST'),
                         'title2' => _t('BLOG_CATEGORIES'));
        $items[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Blog', 'PopularPosts'),
                         'title'  => _t('BLOG_POPULAR_POSTS'));
        $items[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Blog', 'PostsAuthors'),
                         'title'  => _t('BLOG_POSTS_AUTHORS'));

        //Blog model
        $pModel      = $GLOBALS['app']->loadGadget('Blog', 'Model', 'Posts');
        $cModel      = $GLOBALS['app']->loadGadget('Blog', 'Model', 'Categories');
        $categories = $cModel->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
            $max_size = 32;
            foreach ($categories as $cat) {
                $url = $GLOBALS['app']->Map->GetURLFor(
                                            'Blog',
                                            'ShowCategory',
                                            array('id' => empty($cat['fast_url'])?
                                                                $cat['id'] : $cat['fast_url']));
                $items[] = array('url'   => $url,
                                 'title' => ($GLOBALS['app']->UTF8->strlen($cat['name']) > $max_size)?
                                             $GLOBALS['app']->UTF8->substr($cat['name'], 0, $max_size) . '...' :
                                             $cat['name']);
            }
        }

        $entries = $pModel->GetEntries('');
        if (!Jaws_Error::IsError($entries)) {
            $max_size = 32;
            foreach ($entries as $entry) {
                $url = $GLOBALS['app']->Map->GetURLFor(
                                            'Blog',
                                            'SingleView',
                                            array('id' => empty($entry['fast_url'])?
                                                                $entry['id'] : $entry['fast_url']));
                $items[] = array('url'   => $url,
                                 'title' => ($GLOBALS['app']->UTF8->strlen($entry['title']) > $max_size)?
                                             $GLOBALS['app']->UTF8->substr($entry['title'], 0, $max_size) . '...' :
                                             $entry['title']);
            }
        }
        return $items;
    }
}
