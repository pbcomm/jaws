<?php
/**
 * Phoo Gadget
 *
 * @category   Gadget
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Moblog extends Jaws_Gadget_HTML
{
    /**
     * Displays a random image from the gallery listed as a Moblog
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Moblog()
    {
        $tpl = $this->gadget->loadTemplate('Moblog.html');
        $tpl->SetBlock('moblog');
        $tpl->SetVariable('title',_t('PHOO_ACTIONS_MOBLOG'));

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model', 'Moblog');
        $moblog = $model->GetMoblog();
        if (!Jaws_Error::IsError($moblog)) {
            $objDate = $GLOBALS['app']->loadDate();
            include_once JAWS_PATH . 'include/Jaws/Image.php';
            foreach ($moblog as $mb) {
                $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $mb['thumb']);
                if (Jaws_Error::IsError($imgData)) {
                    continue;
                }

                $tpl->SetBlock('moblog/item');
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Phoo',
                    'ViewImage',
                    array('id' => $mb['id'],
                        'albumid' => $mb['phoo_album_id'])));
                $tpl->SetVariable('name',     $mb['name']);
                $tpl->SetVariable('img_desc', $mb['stripped_description']);
                $tpl->SetVariable('filename', $mb['filename']);
                $tpl->SetVariable('width',    $imgData[0]);
                $tpl->SetVariable('height',   $imgData[1]);
                $tpl->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $mb['thumb']));
                $tpl->SetVariable('createtime', $objDate->Format($mb['createtime']));
                $tpl->ParseBlock('moblog/item');
            }
        }
        $tpl->ParseBlock('moblog');
        return $tpl->Get();
    }

}