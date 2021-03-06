<?php
/**
 * Users Core Gadget
 *
 * @category   Gadget
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls LoginBox function if no action is specified
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DefaultAction()
    {
        $userHTML = $GLOBALS['app']->LoadGadget('Users', 'HTML', 'Login');
        return $userHTML->LoginBox();
    }

    /**
     * Builds the NoPermission UI
     *
     * @access  public
     * @param   string  $user    Username
     * @param   string  $gadget  The Gadget user is requesting
     * @param   string  $action  The 'denied' action
     * @return  string  XHTML content
     */
    function ShowNoPermission($user, $gadget, $action)
    {
        // Load the template
        $tpl = $this->gadget->loadTemplate('NoPermission.html', 'index');
        $tpl->SetBlock('NoPermission');
        $tpl->SetVariable('nopermission', _t('USERS_NO_PERMISSION_TITLE'));
        $tpl->SetVariable('description', _t('USERS_NO_PERMISSION_DESC', $gadget, $action));
        $tpl->SetVariable('admin_script', BASE_SCRIPT);
        $tpl->SetVariable('site-name', $this->gadget->registry->fetch('site_name', 'Settings'));
        $tpl->SetVariable('site-slogan', $this->gadget->registry->fetch('site_slogan', 'Settings'));
        $tpl->SetVariable('BASE_URL', $GLOBALS['app']->GetSiteURL('/'));
        $tpl->SetVariable('.dir', _t('GLOBAL_LANG_DIRECTION') == 'rtl' ? '.rtl' : '');
        if ($GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('NoPermission/known');
            $logoutLink = $this->gadget->urlMap('Logout');
            $referLink  = empty($_SERVER['HTTP_REFERER'])?
                $GLOBALS['app']->getSiteURL('/') : Jaws_XSS::filter($_SERVER['HTTP_REFERER']);
            $tpl->SetVariable(
                'known_description',
                _t('USERS_NO_PERMISSION_KNOWN_DESC', $logoutLink, $referLink));
            $tpl->ParseBlock('NoPermission/known');
        } else {
            $tpl->SetBlock('NoPermission/anon');
            $loginLink = $this->gadget->urlMap(
                'LoginBox',
                array('referrer' => Jaws_Utils::getRequestURL(false))
            );
            $referLink = empty($_SERVER['HTTP_REFERER'])?
                $GLOBALS['app']->getSiteURL('/') : Jaws_XSS::filter($_SERVER['HTTP_REFERER']);
            $tpl->SetVariable(
                'anon_description',
                _t('USERS_NO_PERMISSION_ANON_DESC', $loginLink, $referLink));
            $tpl->ParseBlock('NoPermission/anon');
        }
        $tpl->ParseBlock('NoPermission');
        return $tpl->Get();
    }

}