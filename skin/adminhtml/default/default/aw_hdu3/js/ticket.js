var AWHDU3_TICKET_POPUP = Class.create();
AWHDU3_TICKET_POPUP.prototype = {

    initialize: function(content, fireFn, titles) {
        this.contentHtml = content;
        this.fireFn = fireFn;
        this.titles = titles;
    },

    show: function() {
        if (!this.container) {
            this.create();
        }
        this.resize();
        this.overlay.show();
        this.container.show();
    },

    hide: function() {
        this.overlay.hide();
        this.container.hide();
    },

    create: function() {
        this.overlay = new Element('div');
        this.overlay.addClassName('awhdu3-ticket-popup-overlay');

        this.container = new Element('div');
        this.container.addClassName('awhdu3-ticket-popup');

        //header
        this.containerHeader = new Element('div');
        this.containerHeader.addClassName('awhdu3-ticket-popup-header');
        this.containerHeader.update(this.titles.header);
        //content
        this.containerContent = new Element('div');
        this.containerContent.addClassName('awhdu3-ticket-popup-content');
        this.containerContent.innerHTML = this.contentHtml;
        //footer
        this.containerFooter = new Element('div');
        this.containerFooter.addClassName('awhdu3-ticket-popup-footer');
        //error
        this.error = new Element('div');
        this.error.addClassName('awhdu3-ticket-popup-error');

        this.container.appendChild(this.containerHeader);
        this.container.appendChild(this.error);
        this.container.appendChild(this.containerContent);
        this.container.appendChild(this.containerFooter);

        this.cancelButton = new Element('button');
        this.doneButton = new Element('button');
        this.cancelButton.addClassName('back');
        this.cancelButton.update(
            "<span><span>" + this.titles.btnCancel + "</span></span>"
        );
        this.doneButton.update(
            "<span><span>" + this.titles.btnDone + "</span></span>"
        );
        this.containerFooter.appendChild(this.doneButton);
        this.containerFooter.appendChild(this.cancelButton);
        this.hide();
        $$('body').first().appendChild(this.overlay);
        $$('body').first().appendChild(this.container);
        this.container.innerHTML.evalScripts();
        //observe
        this.startObservers();
    },

    remove: function() {
        this.overlay.remove();
        this.container.remove();
        delete this.overlay;
        delete this.container;
    },

    startObservers: function() {
        var me = this;
        Event.observe(this.overlay, 'click', me.hide.bind(me));
        Event.observe(this.cancelButton, 'click', function(){
            me.hide();
            me.unsetError();
        });
        Event.observe(this.doneButton, 'click', me.fireFn);

        Event.observe(window, 'resize', me.resize.bind(me));
        Event.observe(window, 'scroll', me.resize.bind(me));
    },

    setError: function(msg) {
        this.error.update(msg);
        this.error.setStyle({'display': 'block'});
    },

    unsetError: function() {
        this.error.update();
        this.error.setStyle({'display': 'none'});
    },

    resize: function() {
        var xy = this._collectPos(this.container);
        if (xy[0] < 50) {
            xy[0] = 50;
        }
        if (xy[1] < 50) {
            xy[1] = 50;
        }

        var left = xy[0];
        var top = xy[1];

        var isIOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false );
        if (isIOS) {
            this.container.setStyle({'position': 'absolute'});
            left += window.pageXOffset?window.pageXOffset:0;
            top += window.pageYOffset?window.pageYOffset:0;
        }

        this.container.setStyle({
            'left': left + 'px',
            'top': top + 'px'
        });
    },

    _collectPos: function(el) {
        var x, y;

        var elWidth = el.getWidth();
        var docWidth = window.innerWidth;
        x = docWidth/2 - elWidth/2;

        var elHeight = el.getHeight();
        var docHeight = window.innerHeight;
        y = docHeight/2 - elHeight/2;

        return [x, y];
    }
};

var AWHDU3_TICKET_INLINE = Class.create();
AWHDU3_TICKET_INLINE.prototype = {
    initialize: function(el, fireFn, getContentFn, titles) {
        this.el = el;
        this.fireFn = fireFn;
        this.getContentFn = getContentFn;
        this.titles = titles;

        this.el.observe('click', this.show.bind(this));
    },

    create: function(){
        this.container = new Element('div');
        this.container.addClassName('awhdu3-ticket-edit-inline_editable-container');

        this.textarea = new Element('textarea');
        this.cancelButton = new Element('button');
        this.cancelButton.addClassName('back');
        this.saveButton = new Element('button');
        this.cancelButton.update(
            "<span><span>" + this.titles.btnCancel + "</span></span>"
        );
        this.saveButton.update(
            "<span><span>" + this.titles.btnSave + "</span></span>"
        );
        this.hide();
        this.container.appendChild(this.textarea);
        this.container.appendChild(this.saveButton);
        this.container.appendChild(this.cancelButton);

        this.el.up().insert(this.container, {'after': this.el});

        this.cancelButton.observe('click', this.hide.bind(this));
        this.saveButton.observe('click', this.fireFn.bind(this));
    },
    remove: function(){
        this.container.remove();
    },
    show: function(){
        if (!this.container) {
            this.create();
        }
        this.el.hide();
        this.textarea.update(this.getContentFn());
        this.container.show();
    },
    hide: function(){
        this.container.hide();
        this.el.show();
    }
};

var awHDU3TicketGrid = {
    changeAssignee: function(url, id){
        var popup = awHDU3TicketGridAssigneePopupList[id];
        var form = popup.container.select('table').first();
        popup.hide();
        var params = Form.serialize(form, true);
        var me = this;
        new Ajax.Request(url, {
            parameters: params,
            method: 'POST',
            onComplete: function (transport) {
                if (!transport.responseText.isJSON()) {
                    popup.setError('Unable to parse json');
                    popup.show();
                    return;
                }
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        awhdu3TicketGridJsObject.reload();
                        popup.unsetError();
                    } else {
                        popup.setError(response.msg);
                        popup.show();
                    }
                } catch (e) {
                    popup.setError('Ooops, something wrong.');
                    popup.show();
                }
            }
        });
    }
};

var awHDU3TicketView = {
    changeStatus: function(url){
        awHDU3TicketDetailsStatusPopup.hide();
        var params = Form.serialize($('aw-hdu3-ticket-edit-status-change-popup'), true);
        var me = this;
        new Ajax.Request(url, {
            parameters: params,
            method: 'POST',
            onComplete: function (transport) {
                if (!transport.responseText.isJSON()) {
                    awHDU3TicketDetailsStatusPopup.setError('Unable to parse json');
                    awHDU3TicketDetailsStatusPopup.show();
                    return;
                }
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        var el = $$('.awhdu3-ticket-edit-details-status').first();
                        if (response.data.textColor) {
                            el.setStyle({'color': response.data.textColor});
                        }
                        if (response.data.bgColor) {
                            el.setStyle({'background': response.data.bgColor});
                        }
                        el.update(response.data.label);
                        awHDU3TicketDetailsStatusPopup.unsetError();
                        awHDU3TicketDetailsStatusPopup.container.select('textarea').each(function(textarea){
                            textarea.setValue('');
                        });
                        awHDU3TicketThread.ajaxUpdate();
                    } else {
                        awHDU3TicketDetailsStatusPopup.setError(response.msg);
                        awHDU3TicketDetailsStatusPopup.show();
                    }
                } catch (e) {
                    awHDU3TicketDetailsStatusPopup.setError('Ooops, something wrong.');
                    awHDU3TicketDetailsStatusPopup.show();
                }
            }
        });
    },

    changePriority: function(url) {
        awHDU3TicketDetailsPriorityPopup.hide();
        var params = Form.serialize($('aw-hdu3-ticket-edit-priority-change-popup'), true);
        var me = this;
        new Ajax.Request(url, {
            parameters: params,
            method: 'POST',
            onComplete: function (transport) {
                if (!transport.responseText.isJSON()) {
                    awHDU3TicketDetailsPriorityPopup.setError('Unable to parse json');
                    awHDU3TicketDetailsPriorityPopup.show();
                    return;
                }
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        var el = $$('.awhdu3-ticket-edit-details-priority').first();
                        if (response.data.textColor) {
                            el.setStyle({'color': response.data.textColor});
                        }
                        if (response.data.bgColor) {
                            el.setStyle({'background': response.data.bgColor});
                        }
                        el.update(response.data.label);
                        awHDU3TicketDetailsPriorityPopup.unsetError();
                        awHDU3TicketDetailsPriorityPopup.container.select('textarea').each(function(textarea){
                            textarea.setValue('');
                        });
                        awHDU3TicketThread.ajaxUpdate();
                    } else {
                        awHDU3TicketDetailsPriorityPopup.setError(response.msg);
                        awHDU3TicketDetailsPriorityPopup.show();
                    }
                } catch (e) {
                    awHDU3TicketDetailsPriorityPopup.setError('Ooops, something wrong.');
                    awHDU3TicketDetailsPriorityPopup.show();
                }
            }
        });
    },

    changeOrder: function(url) {
        awHDU3TicketDetailsOrderPopup.hide();
        var params = Form.serialize($('aw-hdu3-ticket-edit-order-change-popup'), true);
        var me = this;
        new Ajax.Request(url, {
            parameters: params,
            method: 'POST',
            onComplete: function (transport) {
                if (!transport.responseText.isJSON()) {
                    awHDU3TicketDetailsOrderPopup.setError('Unable to parse json');
                    awHDU3TicketDetailsOrderPopup.show();
                    return;
                }
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        var el = $$('.awhdu3-ticket-edit-details-order').first();
                        el.update(response.data.label);
                        var link = el.up().next('a');
                        if (response.data.orderId) {
                            var newHref = link.href.replace(/order_id\/[0-9]+/, "order_id/" + response.data.orderId);
                            link.setAttribute('href', newHref);
                            link.setStyle({'visibility': 'visible'});
                        } else {
                            link.setStyle({'visibility': 'hidden'});
                        }
                        awHDU3TicketDetailsOrderPopup.unsetError();
                    } else {
                        awHDU3TicketDetailsOrderPopup.setError(response.msg);
                        awHDU3TicketDetailsOrderPopup.show();
                    }
                } catch (e) {
                    awHDU3TicketDetailsOrderPopup.setError('Ooops, something wrong.');
                    awHDU3TicketDetailsOrderPopup.show();
                }
            }
        });
    },

    changeAssignee: function(url){
        awHDU3TicketAssigneePopup.hide();
        var form = awHDU3TicketAssigneePopup.container.select('table').first();
        var params = Form.serialize(form, true);
        var me = this;
        new Ajax.Request(url, {
            parameters: params,
            method: 'POST',
            onComplete: function (transport) {
                if (!transport.responseText.isJSON()) {
                    awHDU3TicketAssigneePopup.setError('Unable to parse json');
                    awHDU3TicketAssigneePopup.show();
                    return;
                }
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        if (response.data.isLocked) {
                            $('awhdu3-ticket-edit-details-agent-locked').setStyle({'display': 'inline-block'});
                        } else {
                            $('awhdu3-ticket-edit-details-agent-locked').setStyle({'display': 'none'});
                        }
                        var agentEl = $('awhdu3-ticket-edit-details-agent-anchor').up().select('strong').first();
                        agentEl.update(response.data.agentLabel);
                        var depEl = $('awhdu3-ticket-edit-details-department-anchor').up().select('strong').first();
                        depEl.update(response.data.departmentLabel);
                        awHDU3TicketAssigneePopup.unsetError();
                        awHDU3TicketAssigneePopup.container.select('textarea').each(function(textarea){
                            textarea.setValue('');
                        });
                        awHDU3TicketThread.ajaxUpdate();
                    } else {
                        awHDU3TicketAssigneePopup.setError(response.msg);
                        awHDU3TicketAssigneePopup.show();
                    }
                } catch (e) {
                    awHDU3TicketAssigneePopup.setError('Ooops, something wrong.');
                    awHDU3TicketAssigneePopup.show();
                }
            }
        });
    },

    addNote: function(url){
        awHDU3TicketThreadNotePopup.hide();
        var params = Form.serialize($('aw-hdu3-ticket-edit-note-add-popup'), true);
        var me = this;
        new Ajax.Request(url, {
            parameters: params,
            method: 'POST',
            onComplete: function (transport) {
                if (!transport.responseText.isJSON()) {
                    awHDU3TicketThreadNotePopup.setError('Unable to parse json');
                    awHDU3TicketThreadNotePopup.show();
                    return;
                }
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        awHDU3TicketThreadNotePopup.unsetError();
                        awHDU3TicketThreadNotePopup.container.select('textarea').each(function(textarea){
                            textarea.setValue('');
                        });
                        awHDU3TicketThread.ajaxUpdate();
                    } else {
                        awHDU3TicketThreadNotePopup.setError(response.msg);
                        awHDU3TicketThreadNotePopup.show();
                    }
                } catch (e) {
                    awHDU3TicketThreadNotePopup.setError('Ooops, something wrong.');
                    awHDU3TicketThreadNotePopup.show();
                }
            }
        });
    },

    changeCustomerInfo: function(url) {
        var me = this;
        new Ajax.Request(url, {
            parameters: {
                content: awHDU3TicketCustomerInfoInline.textarea.getValue()
            },
            method: 'POST',
            onComplete: function (transport) {
                if (!transport.responseText.isJSON()) {
                    alert('Unable to parse json');
                    return;
                }
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        var el = $$('.awhdu3-ticket-edit-customersummary-customernote').first();
                        el.setAttribute('data-original-content', response.data.originalText);
                        el.innerHTML = response.data.text;
                        awHDU3TicketCustomerInfoInline.hide();
                    } else {
                        alert(response.msg);
                    }
                } catch (e) {
                    alert('Ooops, something wrong.');
                }
            }
        });
    },
    insertQuickResponse: function(url) {
        var quickResponseId = $('quick_response').getValue();
        new Ajax.Request(url, {
            parameters: {qr_id: quickResponseId},
            method: 'POST',
            onComplete: function (transport) {
                if (!transport.responseText.isJSON()) {
                    alert('Unable to parse json');
                    return;
                }
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        if (typeof(wysiwygawhdu3_content) != 'undefined' && tinyMCE.get(wysiwygawhdu3_content.id)) {
                            tinyMCE.get(wysiwygawhdu3_content.id).execCommand('mceInsertContent', false, response.data.text);
                        } else if($('awhdu3_content').selectionStart || $('awhdu3_content').selectionStart == '0') {
                            var startPos = $('awhdu3_content').selectionStart;
                            var endPos = $('awhdu3_content').selectionEnd;
                            $('awhdu3_content').value = $('awhdu3_content').value.substring(0, startPos)
                                + response.data.text
                                + $('awhdu3_content').value.substring(endPos, $('awhdu3_content').value.length);
                        } else {
                            $('awhdu3_content').value += response.data.text;
                        }
                    } else {
                        alert(response.msg);
                    }
                } catch (e) {
                    alert('Ooops, something wrong.');
                }
            }
        });
    },
    prepareQuickResponseList: function(optionAvailableList) {
        var availableTemplateIds = [];
        Object.keys(optionAvailableList).each(function(key){
            if($('store_ids').getValue() == key) {
                availableTemplateIds = optionAvailableList[key];
            }
        });
        $$('#quick_response option').each(function(el){
            if (availableTemplateIds.indexOf(el.value) == -1) {
                el.setStyle({display: 'none'})
            } else {
                el.setStyle({display: 'block'})
            }
        });
        if (availableTemplateIds.length) {
            $('quick_response').setStyle({display: 'inline'});
            $('quick_response_list').setStyle({display: 'none'});
            $$('.aw-hdu3-apply-quick-response').first().setStyle({display: 'inline'});
            $('quick_response').setValue(availableTemplateIds[0]);
        } else {
            $('quick_response').setStyle({display: 'none'});
            $('quick_response_list').setStyle({display: 'inline'});
            $$('.aw-hdu3-apply-quick-response').first().setStyle({display: 'none'});
        }
    }
};

var orderTicket = {
    showMessage: function(msg, cssClass) {
        var targetBlock = null;
        var messagesContainer = $('aw-hdu3-messages');
        messagesContainer.update('<ul class="messages"><li class="' + cssClass + '"><ul><li><span>' + msg + '</span></li></ul></li></ul>');
    }
};
