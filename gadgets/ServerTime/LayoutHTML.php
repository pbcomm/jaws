<?php
/**
 * ServerTime Gadget (layout actions for client side)
 *
 * @category   Gadget
 * @package    ServerTime
 * @author     Jonathan Hernandez <ion@suavizado.com>
  * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTime_LayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Displays the server time
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Display()
    {
        $tpl = $this->gadget->loadTemplate('ServerTime.html');
        $tpl->SetBlock('servertime');

        $objDate = $GLOBALS['app']->loadDate();
        $strDate = $objDate->Format(time(),
                                     $this->gadget->registry->fetch('date_format'));
        $tpl->SetVariable('title', _t('SERVERTIME_ACTION_TITLE'));
        $tpl->SetVariable('ServerDateTime', $this->gadget->ParseText($strDate));

        $tpl->ParseBlock('servertime');
        return $tpl->Get();
    }

}