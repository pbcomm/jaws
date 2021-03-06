<?php
/**
 * Forums Gadget
 *
 * @category    Gadget
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <abt_am@yahoo.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Topics extends Forums_HTML
{
    /**
     * Display forum topics
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Topics()
    {
        $rqst = jaws()->request->fetch(array('fid', 'page', 'status'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        $forum  = $fModel->GetForum($rqst['fid']);
        if (Jaws_Error::IsError($forum) || empty($forum)) {
            return false;
        }

        $limit = (int)$this->gadget->registry->fetch('topics_limit');
        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');

        $uid = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $published = null;
        if (empty($uid)) {
            $published = true;
        } else {
            if ($this->gadget->GetPermission('EditOthersTopic')) {
                $uid = null;
            }
            if (!empty($rqst['status'])) {
                if ($rqst['status'] == 'published') {
                    $published = true;
                } else {
                    $published = false;
                }
            }
        }

        $topics = $tModel->GetTopics($forum['id'], $published, $uid, $limit, ($page - 1) * $limit);
        if (Jaws_Error::IsError($topics)) {
            return false;
        }

        $objDate = $GLOBALS['app']->loadDate();
        $tpl = $this->gadget->loadTemplate('Topics.html');
        $tpl->SetBlock('topics');

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
        $tpl->SetVariable('title', $forum['title']);
        $tpl->SetVariable('url', $this->gadget->urlMap('Topics', array('fid' => $forum['id'])));
        $tpl->SetVariable('lbl_topics', _t('FORUMS_TOPICS'));
        $tpl->SetVariable('lbl_replies', _t('FORUMS_REPLIES'));
        $tpl->SetVariable('lbl_views', _t('FORUMS_VIEWS'));
        $tpl->SetVariable('lbl_lastpost', _t('FORUMS_LASTPOST'));

        // date format
        $date_format = $this->gadget->registry->fetch('date_format');
        $date_format = empty($date_format)? 'DN d MN Y' : $date_format;

        // posts per page
        $posts_limit = $this->gadget->registry->fetch('posts_limit');
        $posts_limit = empty($posts_limit) ? 10 : (int)$posts_limit;
        foreach ($topics as $topic) {
            $tpl->SetBlock('topics/topic');
            $tpl->SetVariable('status', (int)$topic['locked']);
            $published_status = ((int)$topic['published'] === 1) ? 'published' : 'draft';
            $tpl->SetVariable('published_status', $published_status);
            $tpl->SetVariable('title', $topic['subject']);
            $tpl->SetVariable(
                'url',
                $this->gadget->urlMap('Posts', array('fid' => $forum['id'], 'tid' => $topic['id']))
            );
            $tpl->SetVariable('replies', $topic['replies']);
            $tpl->SetVariable('views', $topic['views']);
            // first post
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $topic['first_username']);
            $tpl->SetVariable('nickname', $topic['first_nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['first_username']))
            );
            $tpl->SetVariable('firstpost_date', $objDate->Format($topic['first_post_time'], $date_format));
            $tpl->SetVariable('firstpost_date_iso', $objDate->ToISO((int)$topic['first_post_time']));

            // last post
            if (!empty($topic['last_post_id'])) {
                $tpl->SetBlock('topics/topic/lastpost');
                $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
                $tpl->SetVariable('username', $topic['last_username']);
                $tpl->SetVariable('nickname', $topic['last_nickname']);
                $tpl->SetVariable(
                    'user_url',
                    $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['last_username']))
                );
                $tpl->SetVariable('lastpost_lbl',_t('FORUMS_LASTPOST'));
                $tpl->SetVariable('lastpost_date', $objDate->Format($topic['last_post_time'], $date_format));
                $tpl->SetVariable('lastpost_date_iso', $objDate->ToISO((int)$topic['last_post_time']));
                $url_params = array('fid' => $topic['fid'], 'tid'=> $topic['id']);
                $last_post_page = floor(($topic['replies'] - 1)/$posts_limit) + 1;
                if ($last_post_page > 1) {
                    $url_params['page'] = $last_post_page;
                }
                $tpl->SetVariable('lastpost_url', $this->gadget->urlMap('Posts', $url_params));
                $tpl->ParseBlock('topics/topic/lastpost');
            }

            $tpl->ParseBlock('topics/topic');
        }

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'topics',
            $page,
            $limit,
            $forum['topics'],
            _t('FORUMS_TOPICS_COUNT', $forum['topics']),
            'Topics',
            array('fid' => $forum['id'])
        );

        if ($GLOBALS['app']->Session->Logged() && $this->gadget->GetPermission('AddTopic')) {
            $tpl->SetBlock('topics/action');
            $tpl->SetVariable('action_lbl', _t('FORUMS_TOPICS_NEW'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('NewTopic', array('fid' => $forum['id'])));
            $tpl->ParseBlock('topics/action');
        }

        $tpl->ParseBlock('topics');
        return $tpl->Get();
    }

    /**
     * Show new topic form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function NewTopic()
    {
        return $this->EditTopic();
    }

    /**
     * Show edit topic form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function EditTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('fid', 'tid', 'target', 'subject', 'message', 'update_reason'));
        if (empty($rqst['fid'])) {
            return false;
        }

        $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
        if (!empty($rqst['tid'])) {
            $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
            $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
            if (Jaws_Error::IsError($topic) || empty($topic)) {
                return false;
            }

            $title = _t('FORUMS_TOPICS_EDIT_TITLE');
            $btn_title = _t('FORUMS_TOPICS_EDIT_BUTTON');
        } else {
            $forum = $fModel->GetForum($rqst['fid']);
            if (Jaws_Error::IsError($forum) || empty($forum)) {
                return false;
            }

            $topic = array();
            $topic['id'] = 0;
            $topic['fid'] = $forum['id'];
            $topic['forum_title'] = $forum['title'];
            $topic['subject'] = '';
            $topic['message'] = '';
            $topic['update_reason'] = '';
            $title = _t('FORUMS_TOPICS_NEW_TITLE');
            $btn_title = _t('FORUMS_TOPICS_NEW_BUTTON');
        }

        $tpl = $this->gadget->loadTemplate('EditTopic.html');
        $tpl->SetBlock('topic');

        $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
        $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
        $tpl->SetVariable('forum_title', $topic['forum_title']);
        $tpl->SetVariable(
            'forum_url',
            $this->gadget->urlMap('Topics', array('fid' => $topic['fid']))
        );
        $tpl->SetVariable('title', $title);
        $tpl->SetVariable('fid', $rqst['fid']);
        $tpl->SetVariable('tid', $topic['id']);


        // preview
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $topic['target']  = $rqst['target'];
            $topic['subject'] = $rqst['subject'];
            $topic['message'] = $rqst['message'];
            $topic['update_reason'] = $rqst['update_reason'];
            $tpl->SetBlock('topic/preview');
            $tpl->SetVariable('lbl_preview', _t('GLOBAL_PREVIEW'));
            $tpl->SetVariable('message', $this->gadget->ParseText($topic['message'], 'Forums', 'index'));
            $tpl->ParseBlock('topic/preview');
        }

        // response
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('UpdateTopic')) {
            $tpl->SetBlock('topic/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('topic/response');
        }

        // first post meta
        if (!empty($topic['id'])) {
            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;
            // post meta data
            $tpl->SetBlock('topic/post_meta');
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $topic['username']);
            $tpl->SetVariable('nickname', $topic['nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['username']))
            );
            $objDate = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('insert_time', $objDate->Format($topic['first_post_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$topic['first_post_time']));
            $tpl->ParseBlock('topic/post_meta');
        }

        // move topic
        if (!empty($topic['id']) && $this->gadget->GetPermission('MoveTopic')) {
            $tpl->SetBlock('topic/target');
            $topic['target'] = isset($topic['target'])? $topic['target'] : $topic['fid'];
            $tpl->SetVariable('lbl_target', _t('FORUMS_TOPICS_MOVEDTO'));
            $forums = $fModel->GetForums(false, true);
            foreach ($forums as $forum) {
                $tpl->SetBlock('topic/target/item');
                $tpl->SetVariable('fid', $forum['id']);
                $tpl->SetVariable('title', $forum['title']);
                if ($forum['id'] == $topic['target']) {
                    $tpl->SetVariable('selected', 'selected="selected"');
                } else {
                    $tpl->SetVariable('selected', '');
                }
                $tpl->ParseBlock('topic/target/item');
            }
            $tpl->ParseBlock('topic/target');
        }

        // subject
        $tpl->SetBlock('topic/subject');
        $tpl->SetVariable('subject', $topic['subject']);
        $tpl->SetVariable('lbl_subject', _t('FORUMS_TOPICS_SUBJECT'));
        $tpl->ParseBlock('topic/subject');

        // message
        $tpl->SetVariable('lbl_message', _t('FORUMS_POSTS_MESSAGE'));
        $message =& $GLOBALS['app']->LoadEditor('Forums', 'message', Jaws_XSS::defilter($topic['message']), false);
        $message->setId('message');
        $message->TextArea->SetRows(8);
        $tpl->SetVariable('message', $message->Get());

        // status (published or draft)
        if ($this->gadget->GetPermission('PublishTopic')) {
            $tpl->SetBlock('topic/status');
            $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
            $tpl->SetVariable('lbl_draft', _t('GLOBAL_DRAFT'));
            $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
            $tpl->ParseBlock('topic/status');
        }

        // attachment
        if ($this->gadget->registry->fetch('enable_attachment') == 'true' &&
            $this->gadget->GetPermission('AddPostAttachment'))
        {
            $tpl->SetBlock('topic/attachment');
            $tpl->SetVariable('lbl_attachment',_t('FORUMS_POSTS_ATTACHMENT'));
            $tpl->SetVariable('lbl_remove_attachment',_t('FORUMS_POSTS_ATTACHMENT_REMOVE'));
            $tpl->ParseBlock('topic/attachment');
        }

        // update reason
        if (!empty($topic['id'])) {
            $tpl->SetBlock('topic/update_reason');
            $tpl->SetVariable('lbl_update_reason', _t('FORUMS_POSTS_EDIT_REASON'));
            $tpl->SetVariable('update_reason', $topic['update_reason']);
            $tpl->ParseBlock('topic/update_reason');
        }

        // check captcha only in new topic action
        if (empty($topic['id'])) {
            $htmlPolicy = $GLOBALS['app']->LoadGadget('Policy', 'HTML', 'Captcha');
            $htmlPolicy->loadCaptcha($tpl, 'topic');
        }

        // buttons
        $tpl->SetVariable('btn_update_title', $btn_title);
        $tpl->SetVariable('btn_preview_title', _t('GLOBAL_PREVIEW'));
        $tpl->SetVariable('btn_cancel_title', _t('GLOBAL_CANCEL'));

        $tpl->ParseBlock('topic');
        return $tpl->Get();
    }

    /**
     * Add/Edit a topic
     *
     * @access  public
     */
    function UpdateTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $topic = jaws()->request->fetch(
            array(
                'fid', 'tid', 'target', 'subject', 'message', 'remove_attachment',
                'update_reason', 'status'
            ),
            'post'
        );
        $topic['forum_title'] = '';

        if (empty($topic['subject']) ||  empty($topic['message'])) {
            $GLOBALS['app']->Session->PushSimpleResponse(
                _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'),
                'UpdateTopic'
            );
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // check captcha only in new topic action
        if (empty($topic['tid'])) {
            $htmlPolicy = $GLOBALS['app']->LoadGadget('Policy', 'HTML', 'Captcha');
            $resCheck = $htmlPolicy->checkCaptcha();
            if (Jaws_Error::IsError($resCheck)) {
                $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'UpdateTopic');
                Jaws_Header::Referrer();
            }
        }

        // attachment
        $topic['attachment'] = is_null($topic['remove_attachment'])? null : false;
        if (is_null($topic['attachment']) &&
            $this->gadget->registry->fetch('enable_attachment') == 'true' &&
            $this->gadget->GetPermission('AddPostAttachment'))
        {
            $res = Jaws_Utils::UploadFiles(
                $_FILES,
                JAWS_DATA. 'forums',
                '',
                'php,php3,php4,php5,phtml,phps,pl,py,cgi,pcgi,pcgi5,pcgi4,htaccess',
                null
            );
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushSimpleResponse($res->getMessage(), 'UpdateTopic');
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            if (!empty($res)) {
                $topic['attachment']['host_fname'] = $res['attachment'][0]['host_filename'];
                $topic['attachment']['user_fname'] = $res['attachment'][0]['user_filename'];
            }
        }

        $send_notification = true;
        // edit min/max limit time
        $edit_min_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');
        $edit_max_limit_time = (int)$this->gadget->registry->fetch('edit_max_limit_time');

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        if (empty($topic['tid'])) {
            $fModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Forums');
            $result = $fModel->GetForum($topic['fid']);
            if (!Jaws_Error::IsError($result) && !empty($result)) {

                // check topic publish permission
                $status = $topic['status'];
                $published = false;
                if ($this->gadget->GetPermission('PublishTopic') && $status == 'published') {
                    $published = true;
                }

                $topic['forum_title'] = $result['title'];
                $result = $tModel->InsertTopic(
                    $GLOBALS['app']->Session->GetAttribute('user'),
                    $topic['fid'],
                    $topic['subject'],
                    $topic['message'],
                    $topic['attachment'],
                    $published
                );
            }
            $event_type = 'new';
            $error_message = _t('FORUMS_TOPICS_NEW_ERROR');
        } else {
            $oldTopic = $tModel->GetTopic($topic['tid'], $topic['fid']);
            if (Jaws_Error::IsError($oldTopic) || empty($oldTopic)) {
                // redirect to referrer page
                Jaws_Header::Referrer();
            }

            // check permission for edit topic
            $update_uid = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ((!$this->gadget->GetPermission('EditTopic')) ||
                ($oldTopic['first_post_uid'] != $update_uid &&
                 !$this->gadget->GetPermission('EditOthersTopic')) ||
                ($oldTopic['locked'] && !$this->gadget->GetPermission('EditLockedTopic')) ||
                ((time() - $oldTopic['first_post_time']) > $edit_max_limit_time &&
                 !$this->gadget->GetPermission('EditOutdatedTopic'))
            ) {
                return Jaws_HTTPError::Get(403);
            }

            if ((time() - $oldTopic['first_post_time']) <= $edit_min_limit_time) {
                $update_uid = 0;
                $send_notification = false;
                $topic['update_reason'] = '';
            }

            // set target topic for move
            if (!$this->gadget->GetPermission('MoveTopic') || empty($topic['target'])) {
                $topic['target'] = $topic['fid'];
            }

            $topic['forum_title'] = $oldTopic['forum_title'];
            $result = $tModel->UpdateTopic(
                $topic['target'],
                $topic['fid'],
                $topic['tid'],
                $oldTopic['first_post_id'],
                $update_uid,
                $topic['subject'],
                $topic['message'],
                $topic['attachment'],
                $oldTopic['attachment_host_fname'],
                $topic['published'],
                $topic['update_reason']
            );

            // fill forum id with target forum id
            if ($topic['fid'] != $topic['target']) {
                $topic['fid'] = $topic['target'];
                $event_type = 'move';
            } else {
                $event_type = 'edit';
            }

            $error_message = _t('FORUMS_TOPICS_EDIT_ERROR');
        }

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($error_message, 'UpdateTopic');
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $topic['tid'] = $result;
        $topic_link = $this->gadget->urlMap(
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['tid']),
            true
        );

        if ($send_notification) {
            $result = $tModel->TopicNotification(
                $event_type,
                $topic['forum_title'],
                $topic_link,
                $topic['subject'],
                $this->gadget->ParseText($topic['message'], 'Forums', 'index')
            );
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }
        }

        // redirect to topic posts page
        Jaws_Header::Location($topic_link);
    }

    /**
     * Delete a topic
     *
     * @access  public
     */
    function DeleteTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('fid', 'tid', 'confirm'));

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic) || empty($topic)) {
            return false;
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            if (!is_null($rqst['confirm'])) {
                // delete min limit time
                $delete_limit_time = (int)$this->gadget->registry->fetch('edit_min_limit_time');

                // check delete permissions
                if ((!$this->gadget->GetPermission('DeleteTopic')) ||
                    ($topic['first_post_uid'] != (int)$GLOBALS['app']->Session->GetAttribute('user') &&
                     !$this->gadget->GetPermission('DeleteOthersTopic')) ||
                    ((time() - $topic['first_post_time']) > $delete_limit_time &&
                     !$this->gadget->GetPermission('DeleteOutdatedTopic'))
                ) {
                    return Jaws_HTTPError::Get(403);
                }

                $result = $tModel->DeleteTopic($topic['id'], $topic['fid'], $topic['attachment_host_fname']);
                if (Jaws_Error::IsError($result)) {
                    $GLOBALS['app']->Session->PushSimpleResponse(
                        _t('FORUMS_TOPICS_DELETE_ERROR'),
                        'DeleteTopic'
                    );
                    // redirect to referrer page
                    Jaws_Header::Referrer();
                }

                $event_type = 'delete';
                $forum_link = $this->gadget->urlMap(
                    'Topics',
                    array('fid' => $topic['fid']),
                    true
                );
                $result = $tModel->TopicNotification(
                    $event_type,
                    $topic['forum_title'],
                    $forum_link,
                    $topic['subject'],
                    $this->gadget->ParseText($topic['message'], 'Forums', 'index')
                );
                if (Jaws_Error::IsError($result)) {
                    // do nothing
                }

                // redirect to topics list
                Jaws_Header::Location($forum_link);
            }

            // redirect to topic posts list
            Jaws_Header::Location(
                $this->gadget->urlMap('Posts', array('fid'=> $topic['fid'],'tid' => $topic['id']))
            );
        } else {
            $tpl = $this->gadget->loadTemplate('DeleteTopic.html');
            $tpl->SetBlock('topic');

            $tpl->SetVariable('fid', $topic['fid']);
            $tpl->SetVariable('tid', $topic['id']);
            $tpl->SetVariable('findex_title', _t('FORUMS_FORUMS'));
            $tpl->SetVariable('findex_url', $this->gadget->urlMap('Forums'));
            $tpl->SetVariable('forum_title', $topic['forum_title']);
            $tpl->SetVariable(
                'forum_url',
                $this->gadget->urlMap('Topics', array('fid'=> $topic['fid']))
            );
            $tpl->SetVariable('title', _t('FORUMS_TOPICS_DELETE_TITLE'));

            // error response
            if ($response = $GLOBALS['app']->Session->PopSimpleResponse('DeleteTopic')) {
                $tpl->SetBlock('topic/response');
                $tpl->SetVariable('msg', $response);
                $tpl->ParseBlock('topic/response');
            }

            // date format
            $date_format = $this->gadget->registry->fetch('date_format');
            $date_format = empty($date_format)? 'DN d MN Y' : $date_format;
            // post meta data
            $tpl->SetVariable('postedby_lbl',_t('FORUMS_POSTEDBY'));
            $tpl->SetVariable('username', $topic['username']);
            $tpl->SetVariable('nickname', $topic['nickname']);
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor('Users', 'Profile', array('user' => $topic['username']))
            );
            $objDate = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('insert_time', $objDate->Format($topic['first_post_time'], $date_format));
            $tpl->SetVariable('insert_time_iso', $objDate->ToISO((int)$topic['first_post_time']));

            // message
            $tpl->SetVariable('message', $topic['message']);

            $tpl->SetVariable('btn_submit_title', _t('FORUMS_TOPICS_DELETE_BUTTON'));
            $tpl->SetVariable('btn_cancel_title', _t('GLOBAL_CANCEL'));
            $tpl->ParseBlock('topic');
            return $tpl->Get();
        }
    }

    /**
     * Locked a topic
     *
     * @access  public
     */
    function LockTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('fid', 'tid'), 'get');

        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        $result = $tModel->LockTopic($topic['id'], !$topic['locked']);
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        $event_type = $topic['locked']? 'unlock' : 'lock';
        $topic_link = $this->gadget->urlMap(
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['id']),
            true
        );
        $result = $tModel->TopicNotification(
            $event_type,
            $topic['forum_title'],
            $topic_link,
            $topic['subject'],
            $this->gadget->ParseText($topic['message'], 'Forums', 'index')
        );
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        // redirect to referrer page
        Jaws_Header::Referrer();
    }

    /**
     * Publish/Draft a topic
     *
     * @access  public
     */
    function PublishTopic()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $this->gadget->CheckPermission('PublishTopic');

        $rqst = jaws()->request->fetch(array('fid', 'tid'), 'get');
        $tModel = $GLOBALS['app']->LoadGadget('Forums', 'Model', 'Topics');
        $topic = $tModel->GetTopic($rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($topic)) {
            // redirect to referrer page
            Jaws_Header::Referrer();
        }

        // check user permissions
        $uid = (int)$GLOBALS['app']->Session->GetAttribute('user');
        if(!$this->gadget->GetPermission('EditOthersTopic') && ($uid!=$topic['first_post_uid'])) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }
        
        $result = $tModel->PublishTopic($topic['id'], !$topic['published']);
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        $event_type = $topic['published']? 'published' : 'draft';
        $topic_link = $this->gadget->urlMap(
            'Posts',
            array('fid' => $topic['fid'], 'tid' => $topic['id']),
            true
        );
        $result = $tModel->TopicNotification(
            $event_type,
            $topic['forum_title'],
            $topic_link,
            $topic['subject'],
            $this->gadget->ParseText($topic['message'], 'Forums', 'index')
        );
        if (Jaws_Error::IsError($result)) {
            // do nothing
        }

        // redirect to referrer page
        Jaws_Header::Referrer();
    }

}