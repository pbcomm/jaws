<?php
/**
 * Users Installer
 *
 * @category    GadgetModel
 * @package     Users
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Users_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        'latest_limit' => '10',
        'password_recovery' => 'false',
        'register_notification' => 'true',
        'authtype' => 'Default',
        'anon_register' => 'false',
        'anon_repetitive_email' => 'true',
        'anon_activation' => 'user',
        'anon_group' => '',
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageUsers',
        'ManageGroups',
        'ManageOnlineUsers',
        'ManageProperties',
        'ManageUserACLs',
        'ManageGroupACLs',
        'EditUserName',
        'EditUserNickname',
        'EditUserEmail',
        'EditUserPassword',
        'EditUserPersonal',
        'EditUserContacts',
        'EditUserPreferences',
        'ManageAuthenticationMethod',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $variables = array();
        $variables['logon_hours'] = str_pad('', 42, 'F');
        $result = $this->installSchema('schema.xml', $variables);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $new_dir = JAWS_DATA . 'avatar';
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('USERS_NAME'));
        }

        // Create the group 'users'
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User;
        $result = $userModel->AddGroup(
            array(
                'name' => 'users',
                'title' => 'Users',
                'description' => '',
                'enabled' => true,
                'removable' => false
            )
        );
        if (Jaws_Error::IsError($result) && MDB2_ERROR_CONSTRAINT != $result->getCode()) {
            return $result;
        }

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.0.0', '<')) {
            $variables = array();
            $variables['logon_hours'] = str_pad('', 42, 'F');
            $result = $this->installSchema('schema.xml', $variables, '0.8.9.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $sql = "UPDATE [[users]] SET [registered_date] = {now}";
            $result = $GLOBALS['db']->query($sql, array('now'=> time()));
            if (Jaws_Error::IsError($result)) {
                //return $result;
            }

            // Update layout actions
            $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('Users', 'LoginBox', 'LoginBox', 'Login');
                $layoutModel->EditGadgetLayoutAction('Users', 'LoginLinks', 'LoginLinks', 'Login');
            }

            // Registry key
            $this->gadget->registry->insert('latest_limit', '10');

            // ACL keys
            $this->gadget->acl->insert('ManageOnlineUsers');
            $this->gadget->acl->insert('EditUserName');
            $this->gadget->acl->insert('EditUserNickname');
            $this->gadget->acl->insert('EditUserEmail');
            $this->gadget->acl->insert('EditUserPassword');
            $this->gadget->acl->insert('EditUserPersonal');
            $this->gadget->acl->insert('EditUserPreferences');
            $this->gadget->acl->insert('EditUserContacts');
            $this->gadget->acl->delete('EditAccountPassword');
            $this->gadget->acl->delete('EditAccountInformation');
            $this->gadget->acl->delete('EditAccountProfile');
            $this->gadget->acl->delete('EditAccountPreferences');
        }

        return true;
    }

}