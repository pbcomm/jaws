<?php
/**
 * AddressBook gadget hook
 *
 * @category    GadgetHook
 * @package     AddressBook
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2013 Jaws Development Group
 */
class AddressBook_Hooks_Activity extends Jaws_Gadget_Hook
{
    /**
     * Returns public AddressBooks array
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $uname  User's name
     * @return  array   An array of user activity
     */
    function Execute($uid, $uname)
    {
        $entity = array();
        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');
        $addressCount = $model->GetAddressListCount($uid, null, true, null);

        if ($addressCount == 0) {
            return array();
        }

        $entity['title'] = _t('ADDRESSBOOK_PUBLICS');
        $entity['count'] = $addressCount;
        $entity['url'] = $this->gadget->urlMap('AddressList', array('uid' => $uname));

        return array($entity);
    }

}