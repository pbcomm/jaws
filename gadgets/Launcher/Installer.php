<?php
/**
 * Launcher Installer
 *
 * @category    GadgetModel
 * @package     Launcher
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Launcher_Installer extends Jaws_Gadget_Installer
{
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
        if (version_compare($old, '0.9.0', '<')) {
            // Update layout actions
            $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('Launcher', 'Display', 'Execute', 'Execute');
            }
        }

        return true;
    }

}