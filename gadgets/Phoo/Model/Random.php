<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Random extends Phoo_Model
{
    /**
     * Get a random image
     *
     * @access  public
     * @param   int     $albumid    album ID
     * @return  array  The properties of a random image and Jaws_Error on error
     */
    function GetRandomImage($albumid = null)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_image_album');
        $table->select('phoo_album_id', 'filename', 'phoo_image.id',
            'phoo_image.title', 'phoo_image.description');
        $table->join('phoo_image', 'phoo_image.id', 'phoo_image_album.phoo_image_id');

        if (is_numeric($albumid)) {
            $table->and()->where('phoo_image_album.phoo_album_id', (int)$album);
        }
        $table->orderBy($table->random());
        $row = $table->limit(1)->fetchRow();
        if (Jaws_Error::IsError($row) || !isset($row['filename'])) {
            return new Jaws_Error(_t('PHOO_ERROR_RANDOMIMAGE'), _t('PHOO_NAME'));
        }

        $row['name'] = $row['title'];
        $row['thumb'] = $this->GetThumbPath($row['filename']);
        $row['medium'] = $this->GetMediumPath($row['filename']);
        $row['image'] = $this->GetOriginalPath($row['filename']);
        $row['stripped_description'] = strip_tags($row['description']);

        return $row;
    }

}