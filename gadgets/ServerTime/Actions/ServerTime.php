<?php
/**
 * ServerTime Gadget
 *
 * @category   Gadget
 * @package    ServerTime
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTime_Actions_ServerTime extends Jaws_Gadget_HTML
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