<?php
/**
 * Emblems Gadget
 *
 * @category   GadgetModel
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_Model_Emblems extends Jaws_Gadget_Model
{
    /**
     * Get Emblems
     *
     * @access  public
     * @param   bool    $published  if need to get only published emblems
     * @param   mixed   $limit      Optional. Limit of data to retrieve (false = returns all)
     * @return  array   Array of emblems and Jaws_Error on error
     */
    function GetEmblems($published = false, $limit = false)
    {
        if (is_numeric($limit)) {
            $rs = $GLOBALS['db']->setLimit(10, $limit);
            if (Jaws_Error::IsError($rs)) {
                return new Jaws_Error($rs->getMessage(), 'SQL');
            }
        }

        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        $emblemTable->select('id', 'title', 'image', 'url', 'type', 'published:boolean');

        if ($published){
            $emblemTable->where('published', true);
        }
        $res = $emblemTable->orderBy('id asc')->fetchAll();
        if (Jaws_Error::IsError($res)){
            return new Jaws_Error($res->getMessage(), 'SQL');
        }
        return $res;
    }

    /**
     * Get information of an emblem
     *
     * @access  public
     * @param   int     $id  Emblem ID
     * @return  mixed   Array of emblem data and Jaws_Error on error
     */
    function GetEmblem($id)
    {
        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        $emblemTable->select('id:integer', 'title', 'image', 'url', 'type');
        $res = $emblemTable->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }
        return $res;
    }
}