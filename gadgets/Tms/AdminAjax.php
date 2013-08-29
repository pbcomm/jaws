<?php
/**
 * TMS (Theme Management System) AJAX API
 *
 * @category   Ajax
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Tms_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Tms_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
    }

    /**
     * Gets information of given theme
     *
     * @access  public
     * @param   string  $theme  Name of the theme
     * @return  array   Theme info
     */
    function GetThemeInfo($theme)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Tms', 'AdminHTML', 'Themes');
        return $gadget->GetThemeInfo($theme);
    }
}