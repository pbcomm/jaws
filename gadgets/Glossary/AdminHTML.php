<?php
/**
 * Glossary Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Manages the main functions of Glossary administration
     *
     * @access  public
     * @return  string  XHTML template Content
     */
    function Admin()
    {
        $gadgetHTML = $GLOBALS['app']->LoadGadget('Glossary', 'AdminHTML', 'Term');
        return $gadgetHTML->Term();
    }

}