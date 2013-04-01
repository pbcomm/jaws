<?php
/**
 * UrlMapper InstallGadget event
 *
 * @category   Gadget
 * @package    UrlMapper
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Events_InstallGadget extends Jaws_Gadget
{
    /**
     * Event execute method
     *
     */
    function Execute($gadget)
    {
        $uModel = $GLOBALS['app']->loadGadget('UrlMapper', 'AdminModel');
        $res = $uModel->AddGadgetMaps($gadget);
        return $res;
    }

}