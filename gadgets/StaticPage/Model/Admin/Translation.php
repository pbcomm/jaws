<?php
require_once JAWS_PATH . 'gadgets/StaticPage/Model/Translation.php';
/**
 * StaticPage Gadget
 *
 * @category   GadgetModel
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Model_Admin_Translation extends StaticPage_Model_Translation
{
    /**
     * Creates a translation of the given page
     *
     * @access  public
     * @param   mixed   $page_id    ID or fast_url of the page (int/string)
     * @param   string  $title      The translated page title
     * @param   string  $content    The translated page content
     * @param   string  $language   The language we are using
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $published  Publish status of the page
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function AddTranslation($page_id, $title, $content, $language, $meta_keys, $meta_desc, $published)
    {
        // Language exists?
        $language = str_replace(array('.', '/'), '', $language);
        if (!file_exists(JAWS_PATH . "languages/$language/FullName")) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_LANGUAGE_NOT_EXISTS', $language), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_LANGUAGE_NOT_EXISTS', $language), _t('STATICPAGE_NAME'));
        }

        if ($this->TranslationExists($page_id, $language)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_EXISTS', $language), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_EXISTS', $language), _t('STATICPAGE_NAME'));
        }
        $published = $this->gadget->GetPermission('PublishPages')? $published : false;

        $params['base_id'] = $page_id;
        $params['title'] = $title;
        $params['content'] = str_replace("\r\n", "\n", $content);
        $params['language'] = $language;
        $params['user'] = $GLOBALS['app']->Session->GetAttribute('user');
        $params['meta_keywords'] = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['published'] = (bool)$published;
        $params['updated'] = $GLOBALS['db']->Date();

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_ADDED'), _t('STATICPAGE_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_TRANSLATION_CREATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates a translation
     *
     * @access  public
     * @param   int     $id         Translation ID
     * @param   string  $title      The translated page title
     * @param   string  $content    The translated page content
     * @param   string  $language   The language we are using
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $published  Publish status of the page
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateTranslation($id, $title, $content, $language, $meta_keys, $meta_desc, $published)
    {
        //Language exists?
        $language = str_replace(array('.', '/'), '', $language);
        if (!file_exists(JAWS_PATH . "languages/$language/FullName")) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_LANGUAGE_NOT_EXISTS'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_LANGUAGE_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        //Original language?
        $translation = $this->GetPageTranslation($id);
        if (Jaws_Error::isError($translation)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        if ($translation['language'] != $language) {
            if ($this->TranslationExists($translation['base_id'], $language)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_EXISTS'), _t('STATICPAGE_NAME'));
            }

        }

        // check modify other's pages ACL
        if (!$this->gadget->GetPermission('ModifyOthersPages') &&
            ($GLOBALS['app']->Session->GetAttribute('user') != $translation['user']))
        {
            // FIXME: need new language statement
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        // check modify published pages ACL
        if ($translation['published'] &&
            !$this->gadget->GetPermission('ManagePublishedPages'))
        {
            // FIXME: need new language statement
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        // Lets update it
        $params['title']            = $title;
        $params['content']          = str_replace("\r\n", "\n", $content);
        $params['language']         = $language;
        $params['meta_keywords']    = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['updated']          = $GLOBALS['db']->Date();
        if ($this->gadget->GetPermission('PublishPages')) {
            $params['published'] = (bool)$published;
        } else {
            $params['published'] = false;
        }

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->update($params)->where('translation_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED'), _t('STATICPAGE_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_TRANSLATION_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the translation
     *
     * @access  public
     * @param   int     $id Translation ID
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function DeleteTranslation($id)
    {
        $params = array();
        $params['id'] = $id;

        if (!$this->gadget->GetPermission('ModifyOthersPages')) {
            $translation = $this->GetPageTranslation($id);
            if (Jaws_Error::isError($translation)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
            }

            if ($GLOBALS['app']->Session->GetAttribute('user') != $translation['user']) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_DELETED'), _t('STATICPAGE_NAME'));
            }
        }

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->delete()->where('translation_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_TRANSLATION_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_DELETED'), _t('STATICPAGE_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_TRANSLATION_DELETED'), RESPONSE_NOTICE);
        return true;
    }

}