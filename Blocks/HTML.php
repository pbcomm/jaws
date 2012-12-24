<?php
/**
 * Blocks Gadget
 *
 * @category   Gadget
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default text
     *
     * @access  public
     * @return  public   Site's name
     */
    function DefaultAction()
    {
        return $this->gadget->GetRegistry('site_name', 'Settings');
    }

    /**
     * view block(title and content)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewBlock()
    {
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $layout = $GLOBALS['app']->LoadGadget('Blocks', 'LayoutHTML');
        return $layout->Display($id);
    }
}