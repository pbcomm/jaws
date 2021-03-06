<?php
/**
 * PrivateMessage Gadget
 *
 * @category   GadgetInfo
 * @package    PrivateMessage
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.0.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'NavigationArea';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Properties';

}