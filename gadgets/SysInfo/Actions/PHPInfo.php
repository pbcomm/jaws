<?php
/**
 * SysInfo Core Gadget
 *
 * @category   Gadget
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfo_Actions_PHPInfo extends Jaws_Gadget_HTML
{
    /**
     * Displays some common PHP settings like memory limit, safe mode, ...
     *
     * @access  public
     * @return  string XHTML template content
     */
    function PHPInfo()
    {
        if (!$GLOBALS['app']->Session->GetPermission('SysInfo', 'PHPInfo')) {
            return false;
        }

        $model = $GLOBALS['app']->LoadGadget('SysInfo', 'Model', 'PHPInfo');
        $tpl = $this->gadget->loadTemplate('SysInfo.html');
        $tpl->SetBlock('SysInfo');
        $tpl->SetVariable('title',  _t('SYSINFO_PHPINFO'));

        //PHP Settings
        $tpl->SetBlock('SysInfo/InfoSection');
        $items = $model->GetPHPInfo();
        foreach ($items as $item) {
            $tpl->SetBlock('SysInfo/InfoSection/InfoItem');
            $tpl->SetVariable('item_title', $item['title']);
            $tpl->SetVariable('item_value', $item['value']);
            $tpl->ParseBlock('SysInfo/InfoSection/InfoItem');
        }
        $tpl->ParseBlock('SysInfo/InfoSection');

        $tpl->ParseBlock('SysInfo');
        return $tpl->Get();
    }
}