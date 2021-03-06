<?php
/**
 * Layout DisableGadget event
 *
 * @category   Gadget
 * @package    Layout
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Events_DisableGadget extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($gadget)
    {
        $lModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');
        $res = $lModel->PublishGadgetElements($gadget, false);
        return $res;
    }

}