<?php
/**
 * FeedReader - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    FeedReader
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FeedReaderURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('FeedReader', 'DefaultAction'),
                        'title' => _t('FEEDREADER_NAME'));

        $model  = $GLOBALS['app']->loadGadget('FeedReader', 'Model');
        $feeds = $model->GetRSSs();
        if (!Jaws_Error::isError($feeds)) {
            $max_size = 20;
            foreach ($feeds as $feed) {
                $url = $GLOBALS['app']->Map->GetURLFor('FeedReader', 'GetFeed', array('id' => $feed['id']));
                $urls[] = array('url'   => $url,
                                'title' => ($GLOBALS['app']->UTF8->strlen($feed['title']) > $max_size)?
                                            $GLOBALS['app']->UTF8->substr($feed['title'], 0, $max_size).'...' :
                                            $feed['title']);
            }
        }

        return $urls;
    }
}
