<?php
/**
 * Search Installer
 *
 * @category    GadgetModel
 * @package     Search
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        'min_key_len'    => '3',
        'max_result_len' => '500',
        'results_limit'  => '10',
        'searchable_gadgets' => '*',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Uninstall()
    {
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
        if (version_compare($old, '0.9.0', '<')) {
            // Update layout actions
            $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('Search', 'Box', 'Box', 'Search');
                $layoutModel->EditGadgetLayoutAction('Search', 'SimpleBox', 'SimpleBox', 'Search');
                $layoutModel->EditGadgetLayoutAction('Search', 'AdvancedBox', 'AdvancedBox', 'Search');
            }
        }

        return true;
    }

}