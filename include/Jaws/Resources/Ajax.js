/**
 * Ajax base class
 */
var JawsAjax = new Class({
    /**
     * Initiates the Ajax
     *
     * @param   string  gadget      Gadget Name
     * @param   object  callback    Callback functions
     *
     * @return  void
     */
    initialize: function (gadget, callback, baseScript) {
        this.gadget = gadget;
        this.callback = callback;
        if (baseScript === undefined) {
            var href = document.location.href,
                path = document.location.pathname,
                basePath = href.substr(0, href.indexOf(path) + path.length);
            baseScript = basePath.replace($$('base')[0].href, '');
        }
        this.baseURL = baseScript + '?gadget=' + this.gadget + '&action=Ajax&method=';
    },

    /**
     * Performs asynchronous Ajax request
     *
     * @param   string  func    Name of the function to be executed
     * @param   object  params  Parameters passed to the function (optional)
     * @return  void
     */
    callAsync: function (func, params) {
        var options = {};
        options.url = this.baseURL + func;
        options.func = func;
        options.async = true;
        arguments = Array.prototype.slice.call(arguments, 1);
        options.data = toJSON(arguments);
        options.urlEncoded = false;
        options.headers = {'content-type' : 'application/json; charset=utf-8'};
        options.onRequest = this.onRequest.bind(this);
        options.onSuccess = this.onSuccess.bind(this, options);
        options.onFailure = this.onFailure.bind(this, options);
        var req = new Request(options).send();
    },

    /**
     * Performs synchronous Ajax request
     *
     * @param   string  func    Name of the function to be executed
     * @param   object  params  Parameters passed to the function (optional)
     * @return  mixed   Response text on synchronous mode or void otherwise
     */
    callSync: function (func, params) {
        var options = {};
        options.async = false;
        options.url = this.baseURL + func;
        options.func = func;
        arguments = Array.prototype.slice.call(arguments, 1);
        options.data = toJSON(arguments);
        options.urlEncoded = false;
        options.headers = {'content-type' : 'application/json; charset=utf-8'};
        options.onRequest = this.onRequest.bind(this);
        var req = new Request(options).send();
        return eval('(' + req.response.text + ')');
    },

    onRequest: function () {
        // start loading..
    },

    onSuccess: function (reqOptions, responseText) {
        responseText = eval('(' + responseText + ')');
        var reqMethod = this.callback[reqOptions.func];
        if (reqMethod) {
            reqMethod(responseText);
        }
    },

    onFailure: function () {
        // alert failure message
    }

});

/**
 * Repaints a combo
 */
function paintCombo(combo, oddColor, evenColor)
{
    if (evenColor == undefined) {
        evenColor = '#fff';
    }

    var color  = evenColor;
    for(var i=0; i<combo.length; i++) {
        combo.options[i].style.backgroundColor = color;;
        if (i % 2 == 0) {
            color = oddColor;
        } else {
            color = evenColor;
        }
    }
}

/**
 * Change the value of the editor/textarea
 */
function changeEditorValue(name, value)
{
    var usingMCE = typeof tinyMCE  == 'undefined' ? false : true;
    var usingCKE = typeof CKEDITOR == 'undefined' ? false : true;
    if (usingMCE) {
        var editor = tinyMCE.get(name);
        if (editor) {
            editor.setContent(value);
         } else {
            $(name).value = value;
         }
    } else if (usingCKE) {
        var editor = CKEDITOR.instances[name];
        if (editor.status == 'unloaded') {
            $(name).value = value;
        } else {
            editor.setData(value);
        }
    } else {
        $(name).value = value;
    }
}

/**
 * Get the value of the editor/textarea
 */
function getEditorValue(name)
{
    var usingMCE = typeof tinyMCE  == 'undefined' ? false : true;
    var usingCKE = typeof CKEDITOR == 'undefined' ? false : true;
    if (usingMCE) {
        var editor = tinyMCE.get(name);
        return editor.getContent();
    } else if (usingCKE) {
        var editor = CKEDITOR.instances[name];
        if (editor.status != 'unloaded') {
            return editor.getData();
        }
    }

    return $(name).value;
}
/**
 * Extends the Element native object to include some shortcut methods
 */
Element.implement({

    isDisplayed: function(){
        return this.getStyle('display') != 'none';
    },

    isVisible: function(){
        var w = this.offsetWidth,
            h = this.offsetHeight;
        return (w == 0 && h == 0) ? false : (w > 0 && h > 0) ? true : this.style.display != 'none';
    },

    toggle: function(){
        return this[this.isDisplayed() ? 'hide' : 'show']();
    },

    hide: function(){
        var d;
        try {
            //IE fails here if the element is not in the dom
            d = this.getStyle('display');
        } catch(e){}
        if (d == 'none') return this;
        return this.store('element:_originalDisplay', d || '').setStyle('display', 'none');
    },

    show: function(display){
        if (!display && this.isDisplayed()) return this;
        display = display || this.retrieve('element:_originalDisplay') || 'block';
        return this.setStyle('display', (display == 'none') ? 'block' : display);
    },

    swapClass: function(remove, add){
        return this.removeClass(remove).addClass(add);
    }

});

/**
 * Javascript blank string prototype
 */
String.prototype.blank = function() {
    return /^\s*$/.test(this);
}

/**
 * Javascript htmlspecialchars_decode
 *
 * @see https://raw.github.com/kvz/phpjs/master/functions/strings/htmlspecialchars_decode.js
 */
String.prototype.defilter = function(quote_style) {
    var optTemp = 0,
        i = 0,
        noquotes = false;

    if (typeof quote_style === 'undefined') {
        quote_style = 3;
    }

    var str = this.replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };

    if (quote_style === 0) {
        noquotes = true;
    }

    // Allow for a single string or an array of string flags
    if (typeof quote_style !== 'number') {
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            } else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }

    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        str = str.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
        // str = str.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
    }

    if (!noquotes) {
        str = str.replace(/&quot;/g, '"');
    }

    // Put this in last place to avoid escape being double-decoded
    str = str.replace(/&amp;/g, '&');
    return str;
}

/**
 * Reset a (piwi)datagrid:
 *  - Clean all data
 *  - Set the new data
 *  - Repaint
 */
function resetGrid(name, data, rowsSize)
{
    $(name).reset();
    $(name).fillWithArray(data);
    if (rowsSize != undefined) {
        $(name).rowsSize = rowsSize;
    }
    $(name).updatePageCounter();
    $(name).repaint();
}

//Which row selected in DataGrid
var selectedRows = new Array();
var selectedRowsColor = new Array();

/**
 * Select a (piwi)datagrid row
 */
function selectGridRow(name, rowElement)
{
    if (selectedRows[name]) {
        if (typeof(selectedRows[name]) == 'object') {
            selectedRows[name].style.backgroundColor = selectedRowsColor[name];
        } else {
            $(selectedRows[name]).style.backgroundColor = selectedRowsColor[name];
        }
    }

    if (typeof(rowElement) == 'object') {
        selectedRowsColor[name] = rowElement.style.backgroundColor;
        rowElement.style.backgroundColor = '#ffffcc';
    } else {
        selectedRowsColor[name] = $(rowElement).style.backgroundColor;
        $(rowElement).style.backgroundColor = '#ffffcc';
    }

    selectedRows[name] = rowElement;
}

/**
 * Unselect a (piwi)datagrid row
 *
 */
function unselectGridRow(name)
{
    if (selectedRows[name]) {
        if (typeof(selectedRows[name]) == 'object') {
            selectedRows[name].style.backgroundColor = selectedRowsColor[name];
        } else {
            $(selectedRows[name]).style.backgroundColor = selectedRowsColor[name];
        }
    }

    selectedRows[name] = null;
    selectedRowsColor[name] = null;
}

/**
 * Class JawsDatagrid
 */
var JawsDataGrid = {

    /**
     * Get the first Values and prepares the datagrid
     */
    getFirstValues: function() {
        var firstValues = $(this.name).getFirstPagerValues();
        var ajaxObject  = $(this.name).objectName;
        var result      = ajaxObject.callSync('getdata', firstValues, $(this.name).id);
        resetGrid($(this.name), result);
        $(this.name).firstPage();
    },

    /**
     * Get the previous Values and prepares the datagrid
     */
    getPreviousValues: function() {
        var previousValues = $(this.name).getPreviousPagerValues();
        var ajaxObject     = $(this.name).objectName;
        var result         = ajaxObject.callSync('getdata', previousValues, $(this.name).id);
        resetGrid($(this.name), result);
        $(this.name).previousPage();
    },

    /**
     * Get the next Values and prepares the datagrid
     */
    getNextValues: function() {
        var nextValues     = $(this.name).getNextPagerValues();
        var ajaxObject     = $(this.name).objectName;
        var result         = ajaxObject.callSync('getdata', nextValues, $(this.name).id);
        resetGrid($(this.name), result);
        $(this.name).nextPage();
    },

    /**
     * Get the last Values and prepares the datagrid
     */
    getLastValues: function() {
        var lastValues = $(this.name).getLastPagerValues();
        var ajaxObject = $(this.name).objectName;
        var result     = ajaxObject.callSync('getdata', lastValues, $(this.name).id);
        resetGrid($(this.name), result);
        $(this.name).lastPage();
    },

    /**
     * Only retrieves information with the current page the pager has and prepares the datagrid
     */
    getData: function() {
        var currentPage = $(this.name).getCurrentPage();
        var ajaxObject  = $(this.name).objectName;
        var result      = ajaxObject.callSync('getdata', currentPage, $(this.name).id);
        resetGrid($(this.name), result);
    }
}

/**
 * Prepares the datagrid with basic data
 */
function initDataGrid(name, objectName, dataFunc)
{
    if ($(name) == undefined || objectName == undefined) {
        return true;
    }

    $(name).objectName = objectName;
    if (dataFunc == undefined) {
        JawsDataGrid.name = name;
        $(name + '_pagerFirstAnchor').onclick = function() {
            JawsDataGrid.getFirstValues();
        }

        $(name + '_pagerPreviousAnchor').onclick = function() {
            JawsDataGrid.getPreviousValues();
        }

        $(name + '_pagerNextAnchor').onclick = function() {
                JawsDataGrid.getNextValues();
        }

        $(name + '_pagerLastAnchor').onclick = function() {
                JawsDataGrid.getLastValues();
        }

        getDG();
    } else {
        $(name).dataFunc = dataFunc;

        $(name + '_pagerFirstAnchor').onclick = function() {
            var offset = $(name).getFirstPagerValues();
            getDG(name, offset);
            $(name).firstPage();
        }

        $(name + '_pagerPreviousAnchor').onclick = function() {
            var offset = $(name).getPreviousPagerValues();
            getDG(name, offset);
            $(name).previousPage();
        }

        $(name + '_pagerNextAnchor').onclick = function() {
            var offset = $(name).getNextPagerValues();
            getDG(name, offset);
            $(name).nextPage();
        }

        $(name + '_pagerLastAnchor').onclick = function() {
            var offset = $(name).getLastPagerValues();
            getDG(name, offset);
            $(name).lastPage();
        }

        getDG(name);
    }
}

/**
 * Fast method to retrieve datagrid data
 */
function getDG(name, offset, reset)
{
    if (name == undefined) {
        JawsDataGrid.getData();
    } else {
        dataFunc = eval($(name).dataFunc);

        if (offset == undefined) {
            var offset = $(name).getCurrentPage();
        }

        reset = (reset == true) || ($(name).rowsSize == 0);
        dataFunc(name, offset, reset);
        if (reset) {
            $(name).setCurrentPage(0);
        }
    }
}


/**
 * Changes the text of a button with a stock
 */
function changeButtonText(button, message)
{
    var buttonInner = button.innerHTML.substr(0, button.innerHTML.indexOf("&nbsp;"));
    button.innerHTML = buttonInner + "&nbsp;" + message;
    button.value     = message;
}

/**
 * Similar to PHP-trim fuction
 */
function jawsTrim(str)
{
    return str.replace(/^\s*|\s*$/g, '');
}

function initJawsObservers()
{
    var forms = document.getElementsByTagName('form');
    for(var i=0; i<forms.length; i++) {
        new Form.Observer(forms[i], 1, jawsFormCallback);
    }
}


function jawsFormCallback(form, elements)
{
    elements = elements.split('&');
    for(var i=0; i<elements.length; i++) {
        var elementName = elements[i].split('=')[0];
        var element     = form.elements[elementName];
        switch(element.type) {
        case 'text':
            element.value = element.value.unescapeHTML();
            break;
        }
        //var element = $(element
        //alert($elements.type);
    }
}

/**
 * Creates an image link
 *
 *   <a href="link"><img src="imgSrc" border="0" title="text" /></a>
 */
function createImageLink(imgSrc, link, text, space)
{
    var linkElement = document.createElement('a');
    linkElement.href = link;
    if (space == true) {
        linkElement.style.paddingRight = '3px';
    }

    var image = document.createElement('img');
    image.border = '0';
    image.src = imgSrc;
    image.title = text;

    linkElement.appendChild(image);

    return linkElement;
}

/**
 * Resets a combo
 */
function resetCombo(combo)
{
    while(combo.options.length != 0) {
        combo.options[0] = null;
    }
}

/**
 * Prepares the datepicker with basic data
 */
function initDatePicker(name)
{
    dpTable = $(name + '_table');
    var script = dpTable.nextSibling;
    var newScript = document.createElement('script');
    newScript.type = "text/javascript";
    newScript.text = script.text;
    Element.destroy(script);
    dpTable.parentNode.appendChild(newScript);
}

/**
 * Show Dialog Box
 */
function showDialogBox(name, dTitle, url, dHeight, dWidth)
{
    var dRect = document.getSize();
    var dLeft = (dWidth  > dRect.x )? 0 : Math.round(dRect.x  / 2 - dWidth  / 2) + 'px';
    var dTop  = (dHeight > dRect.y)? 0 : Math.round(dRect.y / 2 - dHeight / 2) + 'px';

    if ($(name) == undefined) {
        var overlay = new Element('div', {'id':name+'_overlay', 'class':'dialog_box_overlay'}).hide();
        var iframe  = new IFrame({
            src : url,
            id: name + '_iframe',

            styles: {
                height: dHeight+'px',
                width: dWidth+'px',
                border: 'none',
            },

            events: {
                load: function(){
                    hideWorkingNotification();
                    this.getParent().show('block');
                }
            }
        });

        //var iframe  = new Element('iframe', {'id':name+'_iframe', frameborder:0});
        var close   = new Element('span', {'class': 'dialog_box_close'});
        var title   = new Element('div', {'class':'dialog_box_title'}).adopt(dTitle).adopt(close);
        var dialog  = new Element('div', {'id':name, 'class':'dialog_box'}).adopt(title).adopt(iframe).hide();
        // iframe.addEvent('load', function() {
            // hideWorkingNotification();
            // this.getParent().show('block');
            // Event.addEvent(iframe.contentWindow.document, 'keydown', function(e) {
                // if (e.keyCode == Event.KEY_ESC) {
                    // hideDialogBox(this.getParent());
                // }
            // });
        // });
        // iframe.addEvent('cached:load', function() {
            // hideWorkingNotification();
            // dialog.show('block');
        // });
        close.addEvent('click', function() {hideDialogBox(name);});
        overlay.addEvent('mousedown', function(e) {e.stop();});
         document.addEvent('keydown', function(e) {
            var dialog = document.body.getLast();
            if (e.keyCode == Event.KEY_ESC && dialog.isVisible()) {
                hideDialogBox(dialog.id);
            }
        });
        document.body.adopt(overlay);
        document.body.adopt(dialog);
        dialog.setStyles({left:dLeft, top:dTop});
    }

    $(name+'_overlay').show('block');
    showWorkingNotification();
    if ($(name+'_iframe').src == url) {
        $(name+'_iframe').fireEvent('load');
    } else {
        $(name+'_iframe').src = url;
    }
}

/**
 * Hide Dialog Box
 */
function hideDialogBox(name)
{
    $(name).hide();
    $(name+'_overlay').hide();
}

/**
 * Server error handler
 */
function Jaws_Ajax_ServerError(error) 
{
    //Take the error and parse to see if it's a JawsServerError or a bug in the code
    var errorMessage = error.message;
    //JawsServerError pattern
    var pattern = /^\[(.*?)\]\s+(-)\s+(.*?)/;
    //Test..
    if (pattern.test(errorMessage)) {
        var errorSplitted = errorMessage.split(pattern);
        var errorCode     = errorSplitted[1];
        errorMessage      = errorSplitted[4];
        switch(errorCode) {
        case 'NOPERMISSION': //Not granted?
            alert(errorMessage);
            break;
        case 'NOSESSION': //No session?
            // FIXME, using href of base tag instead of admin.php or if empty parsing URL
            window.location = 'admin.php';
            break;
        case 'NOTLOGGED': //Session expired?
            alert(errorMessage + '...');
            // FIXME, using href of base tag instead of admin.php or if empty parsing URL
            window.location = 'admin.php';
            break;            
        }
    }
}

/**
 * Show the response
 */
function showResponse(message, goTop)
{
    if (typeof(goTop) == 'undefined' || goTop) {
        $(document.body).scrollTo(0, 0);
    }

    messages = new Array();
    if (message[0] == undefined) {
        messages[0] = message;
    } else {
        messages = message;
    }

    $('msgbox-wrapper').innerHTML = '';
    for(var i = 0; i < messages.length; i++) {
        var messageDiv = document.createElement('div');
        $('msgbox-wrapper').appendChild(messageDiv);
        messageDiv.innerHTML = messages[i]['message'];
        messageDiv.className = messages[i]['css'];
        messageDiv.id = 'msgbox_'+i;
        messageDiv.fade('show');
        hideResponseBox(messageDiv);
    }
}

/**
 * Hide response boxes - Fast Code
 */
function hideResponseBox(name, timehide)
{
    if (typeof(timehide) == 'undefined') {
        timehide = '3000';
    }

    setTimeout('hideResponseBoxCallback("' + name.id + '")', timehide);
}

/**
 * Hide response boxes - JS Action (callback)
 */
function hideResponseBoxCallback(name)
{
    $(name).fade('out');
}

/**
 * Show working notification.
 */
function showWorkingNotification(msg)
{
    if (!msg) {
        msg = loading_message;
    }
    $('working_notification').innerHTML = msg;
    $('working_notification').style.visibility = 'visible';
    loading_message = default_loading_message;
}

/**
 * Hide working notification
 */
function hideWorkingNotification()
{
    $('working_notification').style.visibility = 'hidden';
}

/* Copyright (c) 2005 JSON.org */
function toJSON(v) {
    var m = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        s = {
            'boolean': function (x) {
                return String(x);
            },
            number: function (x) {
                return isFinite(x) ? String(x) : 'null';
            },
            string: function (x) {
                if (new RegExp("[\x00-\x1f\\\"]").test(x)) {
                    x = x.replace(new RegExp("([\x00-\x1f\\\"])", 'g'), function(a, b) {
                        var c = m[b];
                        if (c) {
                            return c;
                        }
                        c = b.charCodeAt();
                        return '\\u00' +
                            Math.floor(c / 16).toString(16) +
                            (c % 16).toString(16);
                    });
                }
                return '"' + x + '"';
            },
            object: function (x) {
                if (x) {
                    var a = [], b, f, i, l, v;
                    if (x instanceof Array && x[0] != undefined) {
                        a[0] = '[';
                        l = x.length;
                        for (i = 0; i < l; i += 1) {
                            v = x[i];
                            f = s[typeof v];
                            if (f) {
                                v = f(v);
                                if (typeof v == 'string') {
                                    if (b) {
                                        a[a.length] = ',';
                                    }
                                    a[a.length] = v;
                                    b = true;
                                }
                            }
                        }
                        a[a.length] = ']';
                    } else if (x instanceof Object) {
                        a[0] = '{';
                        for (i in x) {
                            v = x[i];
                            f = s[typeof v];
                            if (f) {
                                v = f(v);
                                if (typeof v == 'string') {
                                    if (b) {
                                        a[a.length] = ',';
                                    }
                                    a.push(s.string(i), ':', v);
                                    b = true;
                                }
                            }
                        }
                        a[a.length] = '}';
                    } else {
                        return;
                    }
                    return a.join('');
                }
                return 'null';
            }
        };

    var f = s[typeof v];
    if (f) {
        v = f(v);
        if (typeof v == 'string') {
            return v;
        }
    }
    return null;
}