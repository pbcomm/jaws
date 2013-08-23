<?php
/**
 * Layout Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Admin_Layout extends Jaws_Gadget_HTML
{
    /**
     * Returns the HTML content to manage the layout in the browser
     *
     * @access  public
     * @return  string  XHTML template conent
     */
    function LayoutManager()
    {
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');

        $t_item = $this->gadget->loadTemplate('LayoutManager.html');
        $t_item->SetBlock('working_notification');
        $t_item->SetVariable('loading-message', _t('GLOBAL_LOADING'));
        $working_box = $t_item->ParseBlock('working_notification');
        $t_item->Blocks['working_notification']->Parsed = '';

        $t_item->SetBlock('msgbox-wrapper');
        $responses = $GLOBALS['app']->Session->PopLastResponse();
        if ($responses) {
            foreach ($responses as $msg_id => $response) {
                $t_item->SetBlock('msgbox-wrapper/msgbox');
                $t_item->SetVariable('msg-css', $response['css']);
                $t_item->SetVariable('msg-txt', $response['message']);
                $t_item->SetVariable('msg-id', $msg_id);
                $t_item->ParseBlock('msgbox-wrapper/msgbox');
            }
        }
        $msg_box = $t_item->ParseBlock('msgbox-wrapper');
        $t_item->Blocks['msgbox-wrapper']->Parsed = '';

        $t_item->SetBlock('drag_drop');
        $t_item->SetVariable('empty_section',    _t('LAYOUT_SECTION_EMPTY'));
        $t_item->SetVariable('display_always',   _t('LAYOUT_ALWAYS'));
        $t_item->SetVariable('display_never',    _t('LAYOUT_NEVER'));
        $t_item->SetVariable('displayWhenTitle', _t('LAYOUT_CHANGE_DW'));
        $t_item->SetVariable('actionsTitle',     _t('LAYOUT_ACTIONS'));
        $t_item->SetVariable('confirmDelete',    _t('LAYOUT_CONFIRM_DELETE'));
        $dragdrop = $t_item->ParseBlock('drag_drop');
        $t_item->Blocks['drag_drop']->Parsed = '';

        // Init layout
        $GLOBALS['app']->InstanceLayout();

        $fakeLayout = new Jaws_Layout();
        $fakeLayout->Load();
        $fakeLayout->AddScriptLink('libraries/mootools/core.js');
        $fakeLayout->AddScriptLink('libraries/mootools/more.js');
        $fakeLayout->AddScriptLink('include/Jaws/Resources/Ajax.js');
        $fakeLayout->AddScriptLink('gadgets/Layout/resources/script.js');

        $layoutContent = $fakeLayout->_Template->Blocks['layout']->Content;
        $layoutContent = preg_replace(
            '$<body([^>]*)>$i',
            '<body\1>' . $working_box . $msg_box . $this->getLayoutControls(),
            $layoutContent);
        $layoutContent = preg_replace('$</body([^>]*)>$i', $dragdrop . '</body\1>', $layoutContent);
        $fakeLayout->_Template->Blocks['layout']->Content = $layoutContent;

        $fakeLayout->_Template->SetVariable('site-title', $this->gadget->registry->fetch('site_name', 'Settings'));

        $fakeLayout->AddHeadLink(
            PIWI_URL. 'piwidata/css/default.css',
            'stylesheet',
            'text/css',
            'default'
        );
        $fakeLayout->AddHeadLink(
            'gadgets/Layout/resources/style.css',
            'stylesheet',
            'text/css'
        );

        foreach ($fakeLayout->_Template->Blocks['layout']->InnerBlock as $name => $data) {
            if ($name == 'head') {
                continue;
            }

            $fakeLayout->_Template->SetBlock('layout/'.$name);
            $js_section_array = '<script type="text/javascript">items[\''.$name.'\'] = new Array(); sections.push(\''.$name.'\');</script>';
            $gadgets = $model->GetGadgetsInSection($name);
            if (!is_array($gadgets)) {
                continue;
            }

            foreach ($gadgets as $gadget) {
                if ($gadget['gadget'] == '[REQUESTEDGADGET]') {
                    $t_item->SetBlock('item');
                    $t_item->SetVariable('section_id', $name);
                    $t_item->SetVariable('item_id', $gadget['id']);
                    $t_item->SetVariable('pos', $gadget['layout_position']);
                    $t_item->SetVariable('gadget', _t('LAYOUT_REQUESTED_GADGET'));
                    $t_item->SetVariable('action', '&nbsp;');
                    $t_item->SetVariable('icon', 'gadgets/Layout/images/requested-gadget.png');
                    $t_item->SetVariable('description', _t('LAYOUT_REQUESTED_GADGET_DESC'));
                    $t_item->SetVariable('lbl_display_when', _t('LAYOUT_DISPLAY_IN'));
                    $t_item->SetVariable('display_when', _t('GLOBAL_ALWAYS'));
                    $t_item->SetVariable('void_link', 'return;');
                    $t_item->SetVariable('section_name', $name);
                    $t_item->SetVariable('delete', 'void(0);');
                    $t_item->SetVariable('delete-img', 'gadgets/Layout/images/no-delete.gif');
                    $t_item->SetVariable('item_status', 'none');
                    $t_item->ParseBlock('item');
                } else {
                    $controls = '';
                    $t_item->SetBlock('item');
                    $t_item->SetVariable('section_id', $name);
                    $t_item->SetVariable('pos', $gadget['layout_position']);
                    $t_item->SetVariable('item_id', $gadget['id']);
                    $t_item->SetVariable('base_script_url', $GLOBALS['app']->getSiteURL('/'.BASE_SCRIPT));
                    $t_item->SetVariable('icon', Jaws::CheckImage('gadgets/'.$gadget['gadget'].'/images/logo.png'));
                    $t_item->SetVariable(
                        'delete',
                        "deleteElement('{$gadget['id']}');"
                    );
                    $t_item->SetVariable('delete-img', 'gadgets/Layout/images/delete-item.gif');

                    $actions = $model->GetGadgetLayoutActions($gadget['gadget'], true);
                    if (isset($actions[$gadget['gadget_action']]) &&
                        Jaws_Gadget::IsGadgetEnabled($gadget['gadget'])
                    ) {
                        $t_item->SetVariable('gadget', _t(strtoupper($gadget['gadget']).'_NAME'));
                        if (isset($actions[$gadget['gadget_action']]['name'])) {
                            $t_item->SetVariable('action', $actions[$gadget['gadget_action']]['name']);
                        } else {
                            $t_item->SetVariable('action', $gadget['gadget_action']);
                        }
                        $t_item->SetVariable('description', $actions[$gadget['gadget_action']]['desc']);
                        $t_item->SetVariable('item_status', 'none');
                    } else {
                        $t_item->SetVariable('gadget', $gadget['gadget']);
                        $t_item->SetVariable('action', $gadget['gadget_action']);
                        $t_item->SetVariable('description', $gadget['gadget_action']);
                        $t_item->SetVariable('item_status', 'line-through');
                    }
                    unset($actions);

                    $t_item->SetVariable('controls', $controls);
                    $t_item->SetVariable('void_link', '');
                    $t_item->SetVariable('lbl_display_when', _t('LAYOUT_DISPLAY_IN'));
                    if ($gadget['display_when'] == '*') {
                        $t_item->SetVariable('display_when', _t('GLOBAL_ALWAYS'));
                    } elseif (empty($gadget['display_when'])) {
                        $t_item->SetVariable('display_when', _t('LAYOUT_NEVER'));
                    } else {
                        $t_item->SetVariable('display_when', str_replace(',', ', ', $gadget['display_when']));
                    }
                    $t_item->ParseBlock('item');
                }
            }

            $fakeLayout->_Template->SetVariable(
                'ELEMENT', '<div id="layout_'.$name.'" class="layout-section" title="'.
                $name.'">'.$js_section_array.$t_item->Get().'</div>');

            $fakeLayout->_Template->ParseBlock('layout/'.$name);
            $t_item->Blocks['item']->Parsed = '';
        }

        return $fakeLayout->Get(true);
    }

    /**
     *
     *
     */
    function getLayoutControls()
    {
        $GLOBALS['app']->LoadGadget('ControlPanel', 'AdminHTML');

        $tpl = $this->gadget->loadTemplate('LayoutControls.html');
        $tpl->SetBlock('controls');
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $gInfo  = $GLOBALS['app']->loadGadget('Layout', 'Info');
        $docurl = null;
        if (!Jaws_Error::isError($gInfo)) {
            $docurl = $gInfo->GetDoc();
        }

        $tpl->SetVariable('admin_script', BASE_SCRIPT);
        $tpl->SetVariable('title-cp', _t('GLOBAL_CONTROLPANEL'));
        $tpl->SetVariable('title-name', _t('LAYOUT_NAME'));
        $tpl->SetVariable('icon-gadget', 'gadgets/Layout/images/logo.png');
        $tpl->SetVariable('title-gadget', 'Layout');

        $tpl->SetVariable('theme', _t('LAYOUT_THEME'));
        $themeCombo =& Piwi::CreateWidget('ComboGroup', 'theme');
        $themeCombo->setID('theme');
        $themeCombo->addGroup('local', _t('LAYOUT_THEME_LOCAL'));
        $themeCombo->addGroup('remote', _t('LAYOUT_THEME_REMOTE'));
        $themes = Jaws_Utils::GetThemesList();
        foreach ($themes as $theme => $tInfo) {
            $themeCombo->AddOption($tInfo['local']? 'local' : 'remote', $tInfo['name'], $theme);
        }
        $themeCombo->SetDefault($this->gadget->registry->fetch('theme', 'Settings'));
        $themeCombo->AddEvent(ON_CHANGE, "changeTheme();");
        $themeCombo->SetEnabled($this->gadget->GetPermission('ManageThemes'));
        $tpl->SetVariable('theme_combo', $themeCombo->Get());

        $add =& Piwi::CreateWidget('Button', 'add', _t('LAYOUT_NEW'), STOCK_ADD);
        $url = $GLOBALS['app']->getSiteURL().'/'.BASE_SCRIPT.'?gadget=Layout&amp;action=AddLayoutElement&amp;mode=new';
        $add->AddEvent(ON_CLICK, "addGadget('".$url."', '"._t('LAYOUT_NEW')."');");
        $tpl->SetVariable('add_gadget', $add->Get());

        if (!empty($docurl) && !is_null($docurl)) {
            $tpl->SetBlock('controls/documentation');
            $tpl->SetVariable('src', 'images/stock/help-browser.png');
            $tpl->SetVariable('alt', _t('GLOBAL_READ_DOCUMENTATION'));
            $tpl->SetVariable('url', $docurl);
            $tpl->ParseBlock('controls/documentation');
        }

        $tpl->ParseBlock('controls');
        return $tpl->Get();
    }

    /**
     *
     *
     */
    function ChangeTheme()
    {
        $this->gadget->CheckPermission('ManageThemes');

        $request =& Jaws_Request::getInstance();
        $theme = $request->get('theme', 'post');

        $layout_path = JAWS_THEMES. $theme;
        if (!file_exists($layout_path. '/layout.html')) {
            $layout_path = JAWS_BASE_THEMES. $theme;
        }
        $tpl = $this->gadget->loadTemplate('layout.html', $layout_path);

        // Validate theme
        if (!isset($tpl->Blocks['layout'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'layout'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout&action=Admin');
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['head'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'head'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout&action=Admin');
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['main'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'main'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout&action=Admin');
        }

        // Verify blocks/Reassign gadgets
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');
        $sections = $model->GetLayoutSections();
        foreach ($sections as $section) {
            if (!isset($tpl->Blocks['layout']->InnerBlock[$section])) {
                if (isset($tpl->Blocks['layout']->InnerBlock[$section . '_narrow'])) {
                    $model->MoveSection($section, $section . '_narrow');
                } elseif (isset($tpl->Blocks['layout']->InnerBlock[$section . '_wide'])) {
                    $model->MoveSection($section, $section . '_wide');
                } else {
                    if (strpos($section, '_narrow')) {
                        $clear_section = str_replace('_narrow', '', $section);
                    } else {
                        $clear_section = str_replace('_wide', '', $section);
                    }
                    if (isset($tpl->Blocks['layout']->InnerBlock[$clear_section])) {
                        $model->MoveSection($section, $clear_section);
                    } else {
                        $model->MoveSection($section, 'main');
                    }
                }
            }
        }

        $this->gadget->registry->update('theme', $theme, 'Settings');
        $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_THEME_CHANGED'), RESPONSE_NOTICE);
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout');
    }

    /**
     * Adds layout element
     *
     * @access  public
     * @return  XHTML template content
     */
    function AddLayoutElement()
    {
        // FIXME: When a gadget don't have layout actions
        // doesn't permit to add it into layout
        $tpl = $this->gadget->loadTemplate('AddGadget.html');
        $tpl->SetBlock('template');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $tpl->SetVariable('BASE_URL', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $tpl->SetVariable('gadgets', _t('LAYOUT_GADGETS'));
        $tpl->SetVariable('actions', _t('LAYOUT_ACTIONS'));
        $tpl->SetVariable('no_actions_msg', _t('LAYOUT_NO_GADGET_ACTIONS'));
        $addButton =& Piwi::CreateWidget('Button', 'add',_t('LAYOUT_NEW'), STOCK_ADD);
        $addButton->AddEvent(ON_CLICK, "getAction();");
        $tpl->SetVariable('add_button', $addButton->Get());

        $request =& Jaws_Request::getInstance();
        $section = $request->get('section', 'post');
        if (is_null($section)) {
            $section = $request->get('section', 'get');
            $section = !is_null($section) ? $section : '';
        }

        $tpl->SetVariable('section', $section);

        $cmpModel = $GLOBALS['app']->LoadGadget('Components', 'Model', 'Gadgets');
        $gadget_list = $cmpModel->GetGadgetsList(null, true, true, true);

        //Hold.. if we dont have a selected gadget?.. like no gadgets?
        if (count($gadget_list) <= 0) {
            Jaws_Error::Fatal('You don\'t have any installed gadgets, please enable/install one and then come back',
                __FILE__, __LINE__);
        }

        reset($gadget_list);
        $first = current($gadget_list);
        $tpl->SetVariable('first', $first['name']);

        $tpl->SetBlock('template/working_notification');
        $tpl->SetVariable('loading-message', _t('GLOBAL_LOADING'));
        $tpl->ParseBlock('template/working_notification');

        foreach ($gadget_list as $gadget) {
            $tpl->SetBlock('template/gadget');
            $tpl->SetVariable('id',     $gadget['name']);
            $tpl->SetVariable('icon',   'gadgets/'.$gadget['name'].'/images/logo.png');
            $tpl->SetVariable('gadget', $gadget['title']);
            $tpl->SetVariable('desc',   $gadget['description']);
            $tpl->ParseBlock('template/gadget');
        }

        $tpl->ParseBlock('template');

        return $tpl->Get();
    }

    /**
     * Changes action of a given gadget
     *
     * @access  public
     * @return  XHTML template content
     */
    function EditElementAction()
    {
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');
        $model = $GLOBALS['app']->LoadGadget('Layout', 'AdminModel', 'Layout');
        $layoutElement = $model->GetElement($id);
        if (!$layoutElement || !isset($layoutElement['id'])) {
            return false;
        }

        $tpl = $this->gadget->loadTemplate('EditGadget.html');
        $tpl->SetBlock('template');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $tpl->SetVariable('BASE_URL', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $gInfo = $GLOBALS['app']->LoadGadget($layoutElement['gadget'], 'Info');
        $tpl->SetVariable('gadget', $layoutElement['gadget']);
        $tpl->SetVariable('gadget_name', $gInfo->title);
        $tpl->SetVariable('gadget_description', $gInfo->description);

        $btnSave =& Piwi::CreateWidget('Button', 'ok',_t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "getAction('{$id}', '{$layoutElement['gadget']}');");
        $tpl->SetVariable('save', $btnSave->Get());

        $actionsList =& Piwi::CreateWidget('RadioButtons', 'action_field', 'vertical');
        $actions = $model->GetGadgetLayoutActions($layoutElement['gadget']);
        if (count($actions) > 0) {
            foreach ($actions as $aIndex => $action) {
                $tpl->SetBlock('template/gadget_action');
                $tpl->SetVariable('aindex', $aIndex);
                $tpl->SetVariable('name',   $action['name']);
                $tpl->SetVariable('action', $action['action']);
                $tpl->SetVariable('desc',   $action['desc']);
                $action_selected = $layoutElement['gadget_action'] == $action['action'];
                if($action_selected) {
                    $tpl->SetVariable('action_checked', 'checked="checked"');
                } else {
                    $tpl->SetVariable('action_checked', '');
                }

                if (!empty($action['params'])) {
                    $action_params = unserialize($layoutElement['action_params']);
                    foreach ($action['params'] as $pIndex => $param) {
                        $tpl->SetBlock('template/gadget_action/action_param');
                        $param_name = "action_{$aIndex}_param_{$pIndex}";
                        switch (gettype($param['value'])) {
                            case 'integer':
                            case 'double':
                            case 'string':
                                $element =& Piwi::CreateWidget('Entry', $param_name, $param['value']);
                                $element->SetID($param_name);
                                $element->SetStyle('width:120px;');
                                if ($action_selected) {
                                    $element->SetValue($action_params[$pIndex]);
                                }
                                break;

                            case 'boolean':
                                $element =& Piwi::CreateWidget('CheckButtons', $param_name);
                                $element->AddOption('', 1, $param_name);
                                if ($action_selected && $action_params[$pIndex]) {
                                    $element->setDefault($action_params[$pIndex]);
                                }
                                break;

                            default:
                                $element =& Piwi::CreateWidget('Combo', $param_name);
                                $element->SetID($param_name);
                                foreach ($param['value'] as $value => $title) {
                                    $element->AddOption($title, $value);
                                }
                                if ($action_selected) {
                                    $element->SetDefault($action_params[$pIndex]);
                                }
                        }

                        $tpl->SetVariable('aindex', $aIndex);
                        $tpl->SetVariable('pindex', $pIndex);
                        $tpl->SetVariable('ptitle', $param['title']);
                        $tpl->SetVariable('param',  $element->Get());
                        $tpl->ParseBlock('template/gadget_action/action_param');
                    }
                }

                $tpl->ParseBlock('template/gadget_action');
            }
        } else {
            $tpl->SetBlock('template/no_action');
            $tpl->SetVariable('no_gadget_desc', _t('LAYOUT_NO_GADGET_ACTIONS'));
            $tpl->ParseBlock('template/no_action');
        }

        $tpl->ParseBlock('template');
        return $tpl->Get();
    }

    /**
     * Changes when to display a given gadget
     *
     * @access  public
     * @return  XHTML template content
     */
    function ChangeDisplayWhen()
    {
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');

        $tpl = $this->gadget->loadTemplate('DisplayWhen.html');
        $tpl->SetBlock('template');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $tpl->SetVariable('BASE_URL', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('display_when', _t('LAYOUT_DISPLAY'));

        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $layoutElement = $model->GetElement($id);
        if (is_array($layoutElement) && !empty($layoutElement)) {
            $dw_value = $layoutElement['display_when'];
        }

        $displayCombo =& Piwi::CreateWidget('Combo', 'display_in');
        $displayCombo->AddOption(_t('LAYOUT_ALWAYS'), 'always');
        $displayCombo->AddOption(_t('LAYOUT_ONLY_IN_GADGET'), 'selected');

        if ($dw_value == '*') {
            $displayCombo->SetDefault('always');
            $tpl->SetVariable('selected_display', 'none');
        } else {
            $displayCombo->SetDefault('selected');
            $tpl->SetVariable('selected_display', 'block');
        }
        $displayCombo->AddEvent(ON_CHANGE, "showGadgets();");
        $tpl->SetVariable('display_in_combo', $displayCombo->Get());

        // Display in list
        $selectedGadgets = explode(',', $dw_value);
        // for index...
        $gadget_field =& Piwi::CreateWidget('CheckButtons', 'checkbox_index', 'vertical');
        $gadget_field->AddOption(_t('LAYOUT_INDEX'), 'index', null, in_array('index', $selectedGadgets));
        $cmpModel = $GLOBALS['app']->LoadGadget('Components', 'Model', 'Gadgets');
        $gadget_list = $cmpModel->GetGadgetsList(null, true, true, true);
        foreach ($gadget_list as $g) {
            $gadget_field->AddOption($g['title'], $g['name'], null, in_array($g['name'], $selectedGadgets));
        }
        $tpl->SetVariable('selected_gadgets', $gadget_field->Get());

        $saveButton =& Piwi::CreateWidget('Button', 'ok',_t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, "parent.parent.saveChangeDW(".$id.", getSelectedGadgets());");
        $tpl->SetVariable('save', $saveButton->Get());

        $tpl->ParseBlock('template');
        return $tpl->Get();
    }
}