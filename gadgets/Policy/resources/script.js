/**
 * Policy Javascript actions
 *
 * @category   Ajax
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PolicyCallback = {
    addiprange: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('blocked_ips_datagrid').addItem();
            $('blocked_ips_datagrid').setCurrentPage(0);
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    editiprange: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG();
        }
        showResponse(response);
    },

    deleteiprange: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('blocked_ips_datagrid').deleteItem();
            getDG();
        }
        showResponse(response);
    },

    addagent: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('blocked_agents_datagrid').addItem();
            $('blocked_agents_datagrid').setCurrentPage(0);
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    editagent: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG();
        }
        showResponse(response);
    },

    deleteagent: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('blocked_agents_datagrid').deleteItem();
            getDG();
        }
        showResponse(response);
    },

    ipblockingblockundefined: function(response) {
        showResponse(response);
    },

    agentblockingblockundefined: function(response) {
        showResponse(response);
    },

    updateencryptionsettings: function(response) {
        showResponse(response);
    },

    updateantispamsettings: function(response) {
        showResponse(response);
    },

    updateadvancedpolicies: function(response) {
        showResponse(response);
    }
}

/**
 * Select DataGrid row
 *
 */
function selectDataGridRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#ffffcc';
    selectedRow = rowElement;
}

/**
 * Unselect DataGrid row
 *
 */
function unselectDataGridRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

function toggleCaptcha(field) 
{
    if ($(field + '_captcha').value == 'DISABLED') {
        $(field + '_captcha_driver').disabled = true;
    } else {
        $(field + '_captcha_driver').disabled = false;
    }
}

/**
 * Add/Edit Blocked a IP Range
 */
function saveIPRange()
{
    if ($('from_ipaddress').value.blank()) {
        alert(incompleteFields);
        return false;
    }

    if ($('id').value == 0) {
        PolicyAjax.callAsync(
            'addiprange',
            $('from_ipaddress').value,
            $('to_ipaddress').value,
            $('blocked').value
        );
    } else {
        PolicyAjax.callAsync(
            'editiprange',
            $('id').value,
            $('from_ipaddress').value,
            $('to_ipaddress').value,
            $('blocked').value
        );
    }
}

/**
 * Edit an IP range
 *
 */
function editIPRange(element, id)
{
    currentAction = 'IPBlocking';
    selectDataGridRow(element.parentNode.parentNode);
    var ipRange = PolicyAjax.callSync('getiprange', id);

    $('id').value = ipRange['id'];
    $('from_ipaddress').value = ipRange['from_ip'];
    $('to_ipaddress').value   = ipRange['to_ip'];
    $('blocked').selectedIndex = ipRange['blocked']? 1 : 0;
}

/**
 * Delete an IP range
 */
function deleteIPRange(element, id)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    var answer = confirm(confirmIPRangeDelete);
    if (answer) {
        PolicyAjax.callAsync('deleteiprange', id);
    }
    unselectDataGridRow();
}

/**
 * Add/Edit Blocked Agent
 */
function saveAgent()
{
    if ($('agent').value.blank()) {
        alert(incompleteFields);
        return false;
    }

    if ($('id').value == 0) {
        PolicyAjax.callAsync('addagent', $('agent').value, $('blocked').value);
    } else {
        PolicyAjax.callAsync('editagent', $('id').value, $('agent').value, $('blocked').value);
    }
}

/**
 * Edit a Agent
 *
 */
function editAgent(element, id)
{
    currentAction = 'AgentBlocking';
    selectDataGridRow(element.parentNode.parentNode);
    var agent = PolicyAjax.callSync('getagent', id);

    $('id').value    = agent['id'];
    $('agent').value = agent['agent'].defilter();
    $('blocked').selectedIndex = agent['blocked']? 1 : 0;
}

/**
 * Delete an Agent
 */
function deleteAgent(element, id)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    var answer = confirm(confirmAgentDelete);
    if (answer) {
        PolicyAjax.callAsync('deleteagent', id);
    }
    unselectDataGridRow();
}

/**
 * setIPBlockAnonymous
 */
function setBlockUndefinedIP()
{
    try {
        PolicyAjax.callAsync('ipblockingblockundefined', $('block_undefined_ip').checked);
    } catch(e) {
        alert(e);
    }
}

/**
 * setAgentBlockUndefined
 */
function setBlockUndefinedAgent()
{
    try {
        PolicyAjax.callAsync(
            'agentblockingblockundefined',
            $('block_undefined_agent').checked
        );
    } catch(e) {
        alert(e);
    }
}

/**
 * save encryption settings
 */
function saveEncryptionSettings()
{
    try {
        PolicyAjax.callAsync(
            'updateencryptionsettings',
            $('enabled').value,
            $('key_age').value,
            $('key_len').value
        );
    } catch(e) {
        alert(e);
    }
}

/**
 * save AntiSpam settings
 */
function saveAntiSpamSettings()
{
    try {
        PolicyAjax.callAsync(
            'updateantispamsettings',
            $('filter').value,
            $('default_captcha').value,
            $('default_captcha_driver').value,
            $('obfuscator').value
        );
    } catch(e) {
        alert(e);
    }
}

/**
 * save Advanced Policies
 */
function saveAdvancedPolicies()
{
    try {
        PolicyAjax.callAsync(
            'updateadvancedpolicies',
            $('passwd_complexity').value,
            $('passwd_bad_count').value,
            $('passwd_lockedout_time').value,
            $('passwd_max_age').value,
            $('passwd_min_length').value,
            $('login_captcha').value,
            $('login_captcha_driver').value,
            $('xss_parsing_level').value,
            $('session_idle_timeout').value,
            $('session_remember_timeout').value
        );
    } catch(e) {
        alert(e);
    }
}

/**
 * Submit the form
 */
function submitForm(form)
{
    switch (form.elements['action'].value) {
        case 'AddIPBand':
            addIPBand(form);
            break;
        case 'AddAgent':
            addAgent(form);
            break;
        case 'UpdateProperties':
            updateProperties(form);
            break;
        default:
            break;
    }
}

/**
 * Clean the form
 */
function stopAction()
{
    switch (currentAction) {
        case 'IPBlocking':
            $('id').value = 0;
            $('from_ipaddress').value = '';
            $('to_ipaddress').value   = '';
            unselectDataGridRow();
            break;
        case 'AgentBlocking':
            $('id').value = 0;
            $('agent').value = '';
            unselectDataGridRow();
            break;
        default:
            break;
    }
}

var PolicyAjax = new JawsAjax('Policy', PolicyCallback);

//Which action are we runing?
var currentAction = null;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
