<?php
/**
 * Emblems AJAX API
 *
 * @category   Ajax
 * @package    Emblems
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Emblems_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

    /**
     * Updates the emblem
     *
     * @access  public
     * @param   int     $id     Emblem ID
     * @param   array   $data   Emblem data
     * @return  array   Response array (notice or error)
     */
    function UpdateEmblem($id, $data)
    {
        $this->gadget->CheckPermission('ManageEmblems');
        $model = $GLOBALS['app']->LoadGadget('Emblems', 'AdminModel', 'Emblems');
        $res = $model->UpdateEmblem($id, $data);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_UPDATED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the emblem
     *
     * @access  public
     * @param   int     $id  Emblem id
     * @return  array   Response array (notice or error)
     */
    function DeleteEmblem($id)
    {
        $this->gadget->CheckPermission('ManageEmblems');

        $model = $GLOBALS['app']->LoadGadget('Emblems', 'Model', 'Emblems');
        $emblem = $model->GetEmblem($id);

        $model = $GLOBALS['app']->LoadGadget('Emblems', 'AdminModel', 'Emblems');
        $res = $model->DeleteEmblem($id);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        // delete the file
        if (!empty($emblem['image'])) {
            Jaws_Utils::Delete(JAWS_DATA . 'emblems/' . $emblem['image']);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_DELETED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Fetches a limited array of emblems
     *
     * @access  public
     * @param   int     $limit  Limit of emblems
     * @return  array   An array of emblems
     */
    function GetData($limit)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Emblems', 'AdminHTML', 'Emblems');
        return $gadget->GetEmblems($limit);
    }
}