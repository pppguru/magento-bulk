var AWHDUTicket = Class.create({
    initialize:function (objName) {
        this.messages = [];
        this.quotes = [];
        this.dbmessages = [];
        this.global = window;
        this.global[objName] = this;

        this.selectors = {
            contentValue:'content_value',
            messageBody:'messageBody',
            messageEdit:'messageEdit',
            messageBodyText:'messageBodyText'
        };

        this.options = {
            saveUrl:''
        };

        String.prototype.replaceAll = function (target, replacement) {
            return this.split(target).join(replacement);
        };
    },

    setQuote:function (id, quote) {
        this.quotes[id] = quote;
        return this;
    },

    getQuote:function (id) {
        return typeof(this.quotes[id]) != 'undefined' ? this.quotes[id] : null;
    },

    setMessage:function (id, body) {
        this.messages[id] = body;
        return this;
    },

    getMessage:function (id) {
        return typeof(this.messages[id]) != 'undefined' ? this.messages[id] : null;
    },

    setDBMessage:function (id, body) {
        this.dbmessages[id] = body;
        return this;
    },

    getDBMessage:function (id) {
        return typeof(this.dbmessages[id]) != 'undefined' ? this.dbmessages[id] : null;
    },

    quoteMessage:function (id) {
        var editorMCE = null;
        if (typeof(tinyMCE) != 'undefined')
            editorMCE = tinyMCE.getInstanceById('content_value');
        if (editorMCE == null) {
            var contentTextarea = $(this.selectors.contentValue);
            contentTextarea.value =
                (contentTextarea.value.replace(/\[quot.*?quot]/, '')) +
                    (this.getQuote(id)).trim() + '\r\n';
            contentTextarea.focus();
        } else {
            //TODO: not setContent - set Text in Wysiwyg
            editorMCE.setContent(editorMCE.getContent().replace(/\[quot.*?quot]/, '') + (this.getQuote(id)).trim() + '\r\n');
            return false;
        }
        return this;
    },

    editMessage:function (id) {
        if ($(this.selectors.messageBody + id) && $(this.selectors.messageEdit + id)) {
            var messageBody = $(this.selectors.messageBody + id);
            var messageEdit = $(this.selectors.messageEdit + id);
            messageBody.hide();
            //messageBody.insert(messageEdit);
            messageEdit.show();
        }
    },

    saveMessage:function (id) {
        if ($(this.selectors.messageBody + id) && $(this.selectors.messageBodyText + id)) {
            var messageBody = $(this.selectors.messageBody + id);
            var messageText = $(this.selectors.messageBodyText + id);
            var messageEdit = $(this.selectors.messageEdit + id);
            new Ajax.Request(this.options.saveUrl, {
                parameters:{
                    id:id,
                    text:messageText.value
                },
                onSuccess:function (transport) {
                    try {
                        var resp = transport.responseText.evalJSON();
                        if (resp.s) {
                            this.setMessage(id, resp.text);
                            this.setQuote(id, resp.quotetext);
                            this.setDBMessage(id, resp.dbtext);
                            messageText.value = resp.text;
                            messageBody.insert({after:messageEdit});
                            messageEdit.hide();
                            messageBody.innerHTML = resp.text;
                            messageBody.show();
                        }
                    } catch (ex) {
                    }
                }.bind(this)
            });
        }
    },

    cancelMessage:function (id) {
        if (this.getDBMessage(id) == this.getMessage(id)) {
            var messageEdit = $(this.selectors.messageEdit + id);
            var messageBody = $(this.selectors.messageBody + id);
            messageEdit.hide();
            messageBody.show();
            return;
        }
        if ($(this.selectors.messageBody + id) && $(this.selectors.messageBodyText + id)) {
            var messageBody = $(this.selectors.messageBody + id);
            var messageText = $(this.selectors.messageBodyText + id);
            var messageEdit = $(this.selectors.messageEdit + id);

            messageText.value = this.getDBMessage(id);
            messageBody.insert({after:messageEdit});
            messageEdit.hide();
            messageBody.innerHTML = this.getMessage(id);
            messageBody.show();
        }
    },

    setUrl:function (name, url) {
        url = typeof(url) != 'undefined' ? url : '';
        url = url.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''));
        this.options[name] = url;
    }
});

new AWHDUTicket('awhduticket');


/* OTHER SCRIPTS FROM Block/Adminhtml/Tickets/Edit.php*/

//FOR ORDER INPUT
var AWHDUTicketOrderAutocompleter;
Event.observe(window, 'load', function () {
    if (typeof(AWHDUAjaxFindOrderUrl) != 'undefined') {
        var tmpDiv = document.createElement('div');
        tmpDiv.setAttribute('id', 'order_autocomplete_choices');
        tmpDiv.addClassName('autocomplete');
        document.body.appendChild(tmpDiv);
        AWHDUTicketOrderAutocompleter = new Ajax.Autocompleter(
            'order_incremental_id',
            'order_autocomplete_choices',
            AWHDUAjaxFindOrderUrl,
            {
                paramName:'order_id',
                minChars:3,
                callback:beforeFindOrderRequest,
                afterUpdateElement:afterUpdateOrderInput
            }
        );
    }
    $('order_incremental_id').observe('keyup', function (event) {
        if (event.findElement('input') && event.findElement('input').value.length < 3) {
            $('assign-to-order-note').setStyle({'display':'none'});
            hideButton();
        }
    });
    Event.observe($('view-order-button'), 'click', function () {
        viewOrder();
    });

    checkStatus();

    if ($('customer_id') && ($('customer_id').options.length > 0)) {
        for(var i = 0; i < $('customer_id').options.length; i++) {
            $('customer_id').options[i].text = $('customer_id').options[i].text.replace('&lt;', '<');
            $('customer_id').options[i].text = $('customer_id').options[i].text.replace('&gt;','>');
        }
    }
    if ($('department_saved_id') && ($('department_saved_id').options.length > 0)) {
        for(var i = 0; i < $('department_saved_id').options.length; i++){
            $('department_saved_id').options[i].text = $('department_saved_id').options[i].text.replace('&lt;', '<');
            $('department_saved_id').options[i].text = $('department_saved_id').options[i].text.replace('&gt;','>');
        }
    }

    setTimeout(function() {
        $('content_value').focus();
    }, 500);

    $('templates_button').onclick = function() {
        if($('quick_template').selectedIndex) {
            var urlRequest = AWHDUAjaxGetTemplateContent
                + 'id/' + $('quick_template').getValue()
                +'/ticket_id/' + AWHDUCurrentTicketId;
            new Ajax.Request(urlRequest,
                {
                    method:'get',
                    onSuccess: function(transport) {
                        var response = transport.responseText || '';
                        if (typeof(wysiwygcontent_value) != 'undefined' && tinyMCE.get(wysiwygcontent_value.id)) {
                            wysiwygcontent_value.toggle();
                            pasteTemplate($('content_value'), response);
                            wysiwygcontent_value.toggle();
                        } else {
                            pasteTemplate($('content_value'), response);
                        }
                    }
                }
            );
        } else {
            alert(AWHDUMessageAlertTemplate);
        }
    }
});

function checkStatus() {
    if ($('order_id').getValue() > 0) {
        showButton($('order_id').getValue());
    } else {
        hideButton();
    }
}

function beforeFindOrderRequest(element, entry) {
    if ($('no-exist-order-note')) {
        $('no-exist-order-note').setStyle({'display':'none'});
    }
    hideButton();
    return entry + '&customer_id=' + $('customer_id').getValue() + '&customer_email=' + $('customer_email').getValue();
}

function afterUpdateOrderInput(text, li) {
    var _id = li.getAttribute('id');
    $('order_id').value = _id;
    if (li.hasClassName('belong-to-customer')) {
        $('assign-to-order-note').setStyle({'display':'none'});
    }
    else {
        $('assign-to-order-note').setStyle({'display':'block'});
    }
    checkStatus();
}

function viewOrder() {
    var id = $('order_id').getValue();
    if (id > 0 && !($('view-order-button').hasClassName('disabled'))) {
        var link = AWHDUUrlToOrderView + 'order_id/' + id;
        popWin(link, '_blank', 'width=800px,height=700px,resizable=1,scrollbars=1');
    }
}
function showButton(id) {
    var orderId = $('order_id');
    var button = $('view-order-button');
    button.removeClassName('disabled');
    orderId.setValue(id);
}
function hideButton() {
    var orderId = $('order_id');
    var button = $('view-order-button');
    button.addClassName('disabled');
    orderId.setValue('');
}

//Blockquote click
Event.observe(window, 'load', function () {
    var hduBlockquoteCurrent = null;
    $$('.helpdesk-message blockquote').each(function (el) {
        el.observe('mouseup', function (event) {
            var blockquote = $(event.currentTarget);
            if (blockquote.hasClassName('active')) {
                if (hduBlockquoteCurrent != blockquote) {
                    hduBlockquoteCurrent = null;
                    return;
                }
                blockquote.removeClassName('active');
                blockquote.setAttribute('title', AWHDUMessageBlockquoteOpenTitle); // title messages initialized in AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit
                blockquote.select('blockquote').each(function (el) {
                    el.removeClassName('active');
                    el.setAttribute('title', AWHDUMessageBlockquoteOpenTitle);
                });
            }
            else {
                blockquote.addClassName('active');
                blockquote.setAttribute('title', AWHDUMessageBlockquoteCloseTitle);
                blockquote.select('blockquote').each(function (el) {
                    el.addClassName('active');
                    el.setAttribute('title', AWHDUMessageBlockquoteCloseTitle);
                });
            }
            event.stop();
        });
        el.observe('mousedown', function (event) {
            hduBlockquoteCurrent = $(event.currentTarget);
        });
        el.observe('mousemove', function (event) {
            if ($(event.currentTarget) == hduBlockquoteCurrent) {
                hduBlockquoteCurrent = null;
            }
        });
    });
});

//FOR CUSTOMER INPUT
Event.observe(window, 'load', function () {
    $('assign_button').observe('click', userSuggest);
    $('customer_email').observe('change', userSuggest);
    $('customer_id').observe('change', userSuggest);
    $('customer_id').observe('change', customerChanged);
    //for button hide
    setInterval(function () {
        checkCustomerId();
    }, 500);
});

function checkCustomerId() {
    if ($('customer_id').getValue() && $('customer_id').getValue() != '-1') {
        $$('button.save').each(function (el) {
            el.disabled = false;
            $(el).removeClassName('disabled');
        });
    } else {
        $$('button.save').each(function (el) {
            el.disabled = true;
            $(el).addClassName('disabled');
        });
    }
    if ($('customer_id').getValue().indexOf('@') === -1 && $('customer_id').getValue() > 0) {
        $('customer_account_link').show();
    }
    else {
        $('customer_account_link').hide();
    }
}


function userSuggest() {
    if (typeof(AWHDUUrlToUserSuggest) == 'undefined') {
        return;
    }

    var url = AWHDUUrlToUserSuggest;
    var letters = $('customer_email').value
    if (letters.length < 3) {
        $('customer_id').options.length = 0;
        $('customer_id').hide();
        return;
    }
    var U = url + 'letters/' + encodeURIComponent(letters) + '/' + 'order_id/' + $('order_id').getValue() + '/';
    new Ajax.Request(U, {
        method:'get',
        onSuccess:function (transport) {
            try {
                var currentValue = $('customer_id').getValue();
                var data = eval(transport.responseText);
                $('customer_id').options.length = 0;
                var __isCurrentOptionExist = false;
                for (var i = 0; i < data.length; i++) {
                    if (data[i].value == currentValue) {
                        __isCurrentOptionExist = true;
                    }
                    var el = new Option(data[i].label.replace('&lt;', '<').replace('&gt;', '>'), data[i].value);
                    el.setAttribute('is_belong_to_order', data[i].is_belong_to_order);
                    $('customer_id').options.add(el);
                }
                if (__isCurrentOptionExist) {
                    $('customer_id').setValue(currentValue);
                }
                updateCustomerAccountLink();

                if ($('customer_id').options.length < 1) {
                    $('customer_id').hide()
                    $('no_users_found').show()
                } else {
                    $('customer_id').show();
                    $('no_users_found').hide()
                }

                for (var i = 0; i < data.length; i++) {
                    if ($('customer_id').getValue() == data[i].value) {
                        if (data[i].is_belong_to_order) {
                            $('assign-to-order-note').setStyle({'display':'none'});
                        }
                        else {
                            $('assign-to-order-note').setStyle({'display':'block'});
                        }
                    }
                }
            } catch (e) {

            }
        }
    });
}

function customerChanged(event) {
    var __currentOption = event.target.options[event.target.selectedIndex];
    if (__currentOption.getAttribute('is_belong_to_order') == 'true') {
        $('assign-to-order-note').setStyle({'display':'none'});
    }
    else {
        $('assign-to-order-note').setStyle({'display':'block'});
    }
    updateCustomerAccountLink();
}

function updateCustomerAccountLink() {
    if ($('customer_id').getValue() && $('customer_id').getValue().indexOf('@') === -1) {
        var href = $('customer_account_link').getAttribute('href');
        $('customer_account_link').setAttribute('href', href.replace(/edit\/([^$]*)/, 'edit/id/' + $('customer_id').getValue() + '/'));
    }
}

function saveAndEmail() {
    $('edit_form').email.value = 1;
    editForm.submit($('edit_form').action);
}

function saveAndContinueEdit() {
    editForm.submit($('edit_form').action+'back/edit/');
}

function toggleEditor() {
    if (tinyMCE.getInstanceById('helpdeskultimate_content') == null) {
        tinyMCE.execCommand('mceAddControl', false, 'helpdeskultimate_content');
    } else {
        tinyMCE.execCommand('mceRemoveControl', false, 'helpdeskultimate_content');
    }
}

function pasteTemplate(myField, myValue) {
    myField.value += myValue;
}
