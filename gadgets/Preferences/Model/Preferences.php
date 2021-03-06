<?php
/**
 * Preferences Gadget Model
 *
 * @category   GadgetModel
 * @package    Preferences
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Preferences_Model_Preferences extends Jaws_Gadget_Model
{
    /**
     * Save the cookie, save the world
     *
     * @access   public
     * @param    array  $preferences
     * @param    int    $expire_age
     * @internal param array $Preferences
     * @return   bool    True/False
     */
    function SavePreferences($preferences, $expire_age = 1440)
    {
        $preferences = array_filter($preferences);
        $GLOBALS['app']->Session->SetCookie('preferences', serialize($preferences), $expire_age);
        return true;
    }
}