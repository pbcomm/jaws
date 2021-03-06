<?php
/**
 * Glossary URL maps
 *
 * @category   GadgetMaps
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('DefaultAction', 'glossary');
$maps[] = array(
    'ViewTerm', 
    'glossary/{term}',
    array('term' => '[\p{L}[:digit:]-_\.]+',)
);
