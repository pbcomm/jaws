<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Actions_Files extends Jaws_Gadget_HTML
{
    /**
     * Builds the file management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function FileForm()
    {
        $mode = jaws()->request->fetch('mode', 'post');
        $tpl = $this->gadget->loadTemplate('File.html');
        $tpl->SetBlock($mode);
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_url', _t('DIRECTORY_FILE_URL'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        if ($mode === 'edit') {
            $tpl->SetVariable('lbl_file', _t('DIRECTORY_FILE'));
            $tpl->SetVariable('lbl_submit', _t('GLOBAL_SUBMIT'));
        }
        if ($mode === 'view') {
            //$tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
            $tpl->SetVariable('lbl_filename', _t('DIRECTORY_FILE_FILENAME'));
            $tpl->SetVariable('lbl_filetype', _t('DIRECTORY_FILE_FILETYPE'));
            $tpl->SetVariable('lbl_filesize', _t('DIRECTORY_FILE_FILESIZE'));
            $tpl->SetVariable('lbl_bytes', _t('DIRECTORY_BYTES'));
            $tpl->SetVariable('lbl_shared', _t('DIRECTORY_SHARE_STATUS'));
            $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
            $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
            $tpl->SetVariable('title', '{title}');
            $tpl->SetVariable('desc', '{description}');
            $tpl->SetVariable('filename', '{filename}');
            $tpl->SetVariable('filetype', '{filetype}');
            $tpl->SetVariable('filesize', '{filesize}');
            $tpl->SetVariable('size', '{size}');
            $tpl->SetVariable('url', '{url}');
            $tpl->SetVariable('is_shared', '{is_shared}');
            $tpl->SetVariable('createtime', '{createtime}');
            $tpl->SetVariable('updatetime', '{updatetime}');
            $tpl->SetVariable('created', '{created}');
            $tpl->SetVariable('modified', '{modified}');
        }
        $tpl->ParseBlock($mode);
        return $tpl->Get();
    }

    /**
     * Creates a new file
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateFile()
    {
        try {
            $data = jaws()->request->fetch(
                array('title', 'description', 'parent', 'url', 'filename', 'filetype', 'filesize')
            );
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['user'] = $data['owner'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data['is_dir'] = false;
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);

            // Upload file
            $path = $GLOBALS['app']->getDataURL('directory/' . $data['user']);
            if (!is_dir($path)) {
                if (!Jaws_Utils::mkdir($path, 2)) {
                    throw new Exception('DIRECTORY_ERROR_FILE_UPLOAD');
                }
            }
            $res = Jaws_Utils::UploadFiles($_FILES, $path);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            } else if ($res !== false) {
                $data['filename'] = $res['file'][0]['host_filename'];
                $data['filetype'] = $res['file'][0]['host_filetype'];
                $data['filesize'] = $res['file'][0]['host_filesize'];
            } else {
                if (empty($data['filename'])) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                } else {
                    $filename = Jaws_Utils::upload_tmp_dir(). '/' . $data['filename'];
                    if (file_exists($filename)) {
                        $target = $path . '/' . $data['filename'];
                        $res = Jaws_Utils::rename($filename, $target, false);
                        if ($res === false) {
                            throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                        }
                        $data['filename'] = basename($res);
                    } else {
                        throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                    }
                }
            }

            // Insert record
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $res = $model->InsertFile($data);
            if (Jaws_Error::IsError($res)) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_CREATE'));
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_FILE_CREATED'), RESPONSE_NOTICE);
    }

    /**
     * Updates file
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateFile()
    {
        try {
            $id = (int)jaws()->request->fetch('id');
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');

            // Check for existance
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($file)) {
                throw new Exception($file->getMessage());
            }
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ($file['user'] != $user) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }

            $data = jaws()->request->fetch(
                array('title', 'description', 'parent', 'url', 'filename', 'filetype', 'filesize')
            );
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);

            // File upload
            $path = $GLOBALS['app']->getDataURL('directory/' . $user);
            if (!is_dir($path)) {
                if (!Jaws_Utils::mkdir($path, 2)) {
                    throw new Exception('DIRECTORY_ERROR_FILE_UPLOAD');
                }
            }
            $res = Jaws_Utils::UploadFiles($_FILES, $path);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            } else if ($res !== false) {
                $data['filename'] = $res['file'][0]['host_filename'];
                $data['filetype'] = $res['file'][0]['host_filetype'];
                $data['filesize'] = $res['file'][0]['host_filesize'];
            } else {
                if ($data['filename'] === ':nochange:') {
                    unset($data['filename']);
                } else if (empty($data['filename'])) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                } else {
                    $filename = Jaws_Utils::upload_tmp_dir(). '/'. $data['filename'];
                    if (file_exists($filename)) {
                        $target = $path . '/' . $data['filename'];
                        $res = Jaws_Utils::rename($filename, $target, false);
                        if ($res === false) {
                            throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                        }
                        $data['filename'] = basename($res);
                    } else {
                        throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                    }
                }
            }

            // Update record
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $res = $model->UpdateFile($id, $data);
            if (Jaws_Error::IsError($res)) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_FILE_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Deletes file
     *
     * @access  public
     * @return  mixed   Response array or Jaws_Error on error
     */
    function DeleteFile()
    {
        try {
            $id = (int)jaws()->request->fetch('id');
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');

            // Check for existance
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($file)) {
                throw new Exception($file->getMessage());
            }
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ($file['user'] != $user) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_DELETE'));
            }

            // Delete from disk
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            }
            $file = $GLOBALS['app']->getDataURL('directory/' . $file['user'] . '/' . $file['filename']);
            if (file_exists($file)) {
                if (!Jaws_Utils::delete($file)) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_DELETE'));
                }
            }

            // Delete from database
            $res = $model->DeleteFile($id);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse(
                $e->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('DIRECTORY_NOTICE_FILE_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Uploads file to system temp directory
     *
     * @access  public
     * @return  string  JavaScript snippet
     */
    function UploadFile()
    {
        $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir());
        if (Jaws_Error::IsError($res)) {
            $response = array('type' => 'error',
                              'message' => $res->getMessage());
        } else {
            $response = array('type' => 'notice',
                              'filename' => $res['file'][0]['host_filename'],
                              'filetype' => $res['file'][0]['host_filetype'],
                              'filesize' => $res['file'][0]['host_filesize']);
        }

        $response = $GLOBALS['app']->UTF8->json_encode($response);
        return "<script>parent.onUpload($response);</script>";
    }

}