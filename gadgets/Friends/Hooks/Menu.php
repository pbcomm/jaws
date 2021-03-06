<?php
/**
 * Friend - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Friend
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Friends_Hooks_Menu extends Jaws_Gadget_Hook
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
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Friends', 'DefaultAction'),
                        'title' => _t('FRIENDS_NAME'));
        return $urls;
    }

}