<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default admin action
     *
     * @access  public
     * @return  string  XHTML Template content
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('ManagePolls')) {
            $gadgetHTML = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML', 'Poll');
            return $gadgetHTML->Polls();
        } elseif ($this->gadget->GetPermission('ManageGroups')) {
            $gadgetHTML = $GLOBALS['app']->LoadGadget('Poll', 'AdminHTML', 'Group');
            return $gadgetHTML->PollGroups();
        }

        $this->gadget->CheckPermission('ViewReports');
    }

    /**
     * Prepares the poll menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Polls', 'PollGroups', 'Reports');
        if (!in_array($action, $actions)) {
            $action = 'Polls';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManagePolls')) {
            $menubar->AddOption('Polls', _t('POLL_POLLS'),
                                BASE_SCRIPT . '?gadget=Poll&amp;action=Polls', 'gadgets/Poll/images/polls_mini.png');
        }
        if ($this->gadget->GetPermission('ManageGroups')) {
            $menubar->AddOption('PollGroups', _t('POLL_GROUPS'),
                                BASE_SCRIPT . '?gadget=Poll&amp;action=PollGroups', 'gadgets/Poll/images/groups_mini.png');
        }
        if ($this->gadget->GetPermission('ViewReports')) {
            $menubar->AddOption('Reports', _t('POLL_REPORTS'),
                                BASE_SCRIPT . '?gadget=Poll&amp;action=Reports', 'gadgets/Poll/images/reports_mini.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }
}