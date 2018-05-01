var _timeStart = 500;
var _defaultCountToRetrive = 10;
var _heightFactor = 20;
var _skuLiPrefix = 'li_sku_';

/* Key Codes */
var KEY_CODE_UP = 40;
var KEY_CODE_DOWN = 38;
var KEY_CODE_RETURN = 13;
var KEY_CODE_ESCAPE = 27;

var isIE = (navigator.userAgent.indexOf("MSIE") !== -1);

String.prototype.trim = function () {
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

var AWAdvancedReportsSkus = Class.create();
AWAdvancedReportsSkus.prototype = {
    initialize: function (data) {
        this.input = data.input;
        this.loader = data.loader;
        this.dropdown = data.dropdown;
        this.sku_ul = data.sku_ul;
        this.url = data.url;
        this.storeUrl = data.store_url;
        this.limit = data.limit ? data.limit : _defaultCountToRetrive;
        this.li_template = new Template(data.template, /(^|.|\r|\n)({{(\w+)}})/);
        this.timer = null;
        this.ajax = null;
        this.disable_selector = data.disable_selector;
        this.active = null;
        this.skus = new Array();

        document.observe("dom:loaded", (function (event) {
            if ($(this.input)) {
                $(window.document).observe('keypress', (function (event) {
                    if (event.keyCode == KEY_CODE_ESCAPE) {
                        this.closeAdvisor();
                    }
                }).bind(this));
                $(window.document).observe('click', (function (event) {
                    this.closeAdvisor();
                }).bind(this));
                $(this.input).observe('keyup', (function (event) {
                    switch (event.keyCode) {
                        case KEY_CODE_UP:
                            if (!this.isClosed()) {
                                this.active = this.getPrevId();
                                this.renderItems();
                            }
                            break;
                        case KEY_CODE_RETURN:
                            if (!this.isClosed()) {
                                this.selectSku();
                            }
                            break;
                        case KEY_CODE_ESCAPE:
                            if (!this.isClosed()) {
                                this.closeAdvisor();
                            }
                            break;
                        case KEY_CODE_DOWN:
                            if (!this.isClosed()) {
                                this.active = this.getNextId();
                                this.renderItems();
                                break;
                            }
                        default:
                            this.disableRetriving();
                            if (this.timer) {
                                clearTimeout(this.timer);
                                this.timer = null;
                            }
                            this.getSku();
                            if (this.getSku() !== '') {
                                this.timer = setTimeout((function (event) {
                                    this.retriveSkus(this.getSku());
                                    this.timer = null;
                                }).bind(this), _timeStart);
                            }
                    }
                }).bind(this));
            }
        }).bind(this));


    },
    isClosed: function () {
        return (this.active === null) && (!$(this.dropdown).hasClassName('display'));
    },
    isActive: function () {
        return (this.active !== null);
    },
    closeAdvisor: function () {
        this.active = null;
        this.skus = new Array();
        this.showDropDown(false);
    },
    getCaretPos: function () {
        var input = $(this.input);
        if (input) {
            if (input.selectionStart) {
                return input.selectionStart;
            } else if (document.selection) {
                input.focus();

                var r = document.selection.createRange();
                if (r == null) {
                    return 0;
                }

                var re = input.createTextRange(),
                    rc = re.duplicate();
                re.moveToBookmark(r.getBookmark());
                rc.setEndPoint('EndToStart', re);

                return rc.text.length;
            }
        }
        return 0;
    },
    setCaretTo: function (pos) {
        var input = $(this.input);
        if (input) {
            if (input.createTextRange) {
                var range = input.createTextRange();
                range.move("character", pos);
                range.select();
            } else if (input.selectionStart) {
                input.focus();
                input.setSelectionRange(pos, pos);
            }
        }
    },
    selectSku: function () {
        if (!this.isClosed()) {
            var item = this.getItem(this.active);
            if (typeof(item) != 'undefined') {
                var input = $(this.input);
                if (input) {
                    var value = input.value;
                    if (value) {
                        var pos = this.getCaretPos();
                        value = value.split(',');
                        value[this.getSkuIndex()] = item.sku_name;
                        input.value = value.join(',');

                        this.setCaretTo(this.getSkuEndPos(pos));
                        this.storeSelection(item.sku_name);

                    }
                }
            }
        }
        this.closeAdvisor();
    },
    storeSelection: function (sku) {
        var sku = encode_base64(sku);
        new Ajax.Request(this.prepareUrl(this.storeUrl.replace("{{sku}}", sku)), {
            method: 'get',
            onFailure: (function () {
            }).bind(this),
            onComplete: (function () {
            }).bind(this),
            loaderArea: false
        });

    },
    getPrevId: function () {
        var id = 0;
        if (this.isActive()) {
            id = this.getIndex(this.active);
            if (id == this.skus.length - 1) {
                id = 0;
            } else {
                id++;
            }
        }
        return _skuLiPrefix + (id + 1);
    },
    getNextId: function () {
        var id = this.skus.length - 1;
        if (this.isActive()) {
            id = this.getIndex(this.active);
            if (id == 0) {
                id = this.skus.length - 1;
            } else {
                id--;
            }
        }
        return _skuLiPrefix + (id + 1);
    },
    fixIEvent: function (event) {
        if (isIE) {
            event.target = event.srcElement;
            return event;
        } else {
            return event;
        }
    },
    mouseClick: function (event) {
        event = this.fixIEvent(event);
        var el = $(event.target);
        if (el) {
            while (!$(el).hasClassName('is_li')) {
                el = el.parentNode;
            }
            this.active = el.id;
            this.selectSku();
        }
    },
    mouseOver: function (event) {
        event = this.fixIEvent(event);
        var el = $(event.target);
        if (el) {
            while (!$(el).hasClassName('is_li')) {
                el = el.parentNode;
            }
            this.active = el.id;
            this.renderItems();
        }
    },
    mouseOut: function (event) {
        event = this.fixIEvent(event);
        if ($(event.target) && $(event.target).hasClassName('product_sku_dropdown_ul')) {
            this.active = null;
            this.renderItems();
        }
    },
    renderItems: function () {
        $$(this.disable_selector).each((function (element) {
            element.removeClassName('active');
        }).bind(this));
        if (!this.isClosed()) {
            if ($(this.active)) {
                $(this.active).addClassName('active');
            }
        }
    },
    getItem: function (sku_id) {
        var num = this.getIndex(sku_id);
        if (typeof(this.skus[num]) != 'undefined') {
            return this.skus[num];
        }
        return {};
    },
    getIndex: function (sku_id) {
        if (sku_id != null) {
            return (sku_id.replace(_skuLiPrefix, '') - 1);
        }
    },
    getSkuIndex: function () {
        var index = null;
        if ($(this.input)) {
            var value = $(this.input).value;
            value = value.split(",");
            if (value.length > 1) {
                var limit = 0, i = 0;
                do {
                    i++;
                    limit += value[i - 1].length + 1;
                } while (this.getCaretPos() > limit);

                /* Checking a comma */
                if ($(this.input).value.charCodeAt(this.getCaretPos() - 1) == 44) {
                    return i;
                }
                return  i - 1;
            } else {
                return 0;
            }
        }
        return index;
    },
    getSkuEndPos: function (pos) {
        this.setCaretTo(pos);
        var skuIndex = this.getSkuIndex();
        var limit = 0;

        if ($(this.input)) {
            var values = $(this.input).value.split(",");
            if (values.length > 1) {
                for (var i = 0; i <= skuIndex; i++) {
                    limit += values[i].length + 1;
                }
                return limit - 1;
            } else {
                return $(this.input).length - 1;
            }
        }
    },
    getCaretPosInSku: function () {
        var caretAt = this.getCaretPos();
        var skuIndex = this.getSkuIndex();
        var limit = 0;

        if ($(this.input)) {
            var values = $(this.input).value.split(",");
            if (values.length > 1) {
                for (var i = 0; i < skuIndex; i++) {
                    limit += values[i].length + 1;
                }
                return (caretAt - limit) - 1;
            } else {
                return caretAt;
            }
        }
        return null;
    },
    getSku: function () {
        var value = '';
        if ($(this.input)) {
            value = $(this.input).value.split(",");
            value = value[this.getSkuIndex()].slice(0, this.getCaretPosInSku() + 1);
            value = value.trim();
        }
        return value;
    },
    showLoader: function (show) {
        var loader = $(this.loader);
        var input = $(this.input);
        if (loader) {
            if (show) {
                loader.addClassName('display');
                input.addClassName('loading');
                this.moveCaretToEnd(input);
            } else {
                loader.removeClassName('display');
                input.removeClassName('loading');
            }
        }
    },
    prepareUrl: function (url) {
        var str = url;
        if (typeof(str) != 'undefined') {
            return str.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''));
        } else {
            return url;
        }
    },
    moveCaretToEnd: function (el) {
        ////TODO it's not work

//        if (typeof el.selectionStart == "number") {
//            el.selectionStart = el.selectionEnd = el.value.length;
//        } else if (typeof el.createTextRange != "undefined") {
//            el.focus();
//            var range = el.createTextRange();
//            range.collapse(false);
//            range.select();
//        }
    },
    retriveSkus: function (sku) {
        this.showLoader(true);
        sku = encode_base64(sku);
        this.ajax = new Ajax.Request(this.prepareUrl(this.url.replace("{{sku}}", sku).replace("{{limit}}", this.limit)), {
            method: 'get',
            onSuccess: (function (transport) {
                if (transport && transport.responseText) {
                    try {
                        response = eval('(' + transport.responseText + ')');
                        if (response.count && (response.count > 0)) {
                            var count = response.count;
                            var skus = response.skus;
                            this.displayResult(skus, count, response.sku);
                        }
                        if (response.error) {
                            console.debug(response.error);
                        }
                    }
                    catch (e) {
                        response = {};
                    }
                }
            }).bind(this),
            onFailure: (function () {

            }).bind(this),
            onComplete: (function () {
                this.ajax = null;
                this.showLoader(false);
            }).bind(this),
            loaderArea: false
        });
    },
    disableRetriving: function () {
        this.showLoader(false);
        this.showDropDown(false);
        this.closeAdvisor();
    },
    displayResult: function (skus, count, search) {
        if (skus.length) {
            this.showDropDown(true);
            var dropdown = $(this.dropdown);

            var skusHtml = '';
            this.skus = new Array();
            for (var i = 0; i < skus.length; i++) {
                skusHtml += this.li_template.evaluate(this.registerNewSku(skus[i], search));
            }

            var ul = $(this.sku_ul);
            if (ul) {
                ul.innerHTML = skusHtml;
            }

            var _height = 0;

            $$('#' + this.dropdown + ' ul li.is_li').each((function (el) {
                $('overlay_' + $(el).id).style.height = ($(el).getHeight() ? $(el).getHeight() : 0) + 'px';
                $('overlay_' + $(el).id).style.top = _height + 'px';
                _height = _height + ($(el).getHeight() ? $(el).getHeight() : 0);
            }).bind(this));

            if (dropdown) {
                dropdown.style.height = _height + 'px';
            }
        }
    },
    registerNewSku: function (sku_name, search) {
        var newId = this.skus.length + 1;
        var changeTo = sku_name.slice(
            sku_name.toUpperCase().indexOf(search.toUpperCase(), 0),
            sku_name.toUpperCase().indexOf(search.toUpperCase(), 0) + search.length
        );

        var newItem = {
            sku_id: _skuLiPrefix + newId,
            sku_name: sku_name,
            sku_title: sku_name.replace(search, '<span class="search" id="span_span_' + _skuLiPrefix + newId + '" >' + changeTo + '</span>', 'i')
        };

        this.skus.push(newItem);
        return newItem;
    },
    showDropDown: function (show) {
        var dropdown = $(this.dropdown);
        if (dropdown) {
            if (show) {
                _submitEnabled = false;
                dropdown.addClassName('display');
            } else {
                _submitEnabled = true;
                dropdown.removeClassName('display');
            }

        }
    }
}
