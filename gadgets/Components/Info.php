<?php
/**
 * Components (Jaws Management System) Gadget
 *
 * @category   GadgetInfo
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi �ormar <dufuz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Components_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '0.3.0';

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * @var     boolean
     * @access  private
     */
    var $_has_layout = false;

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = false;

}