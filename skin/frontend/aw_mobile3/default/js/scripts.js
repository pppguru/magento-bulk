// Remove 300ms delay when tap
if ('addEventListener' in document) {
    document.addEventListener('DOMContentLoaded', function() {
        FastClick.attach(document.body);
    }, false);
}

Event.observe(document, 'dom:loaded', function(e) {
    var fixBody = $$('body')[0];

    // fix position fixed bug on iOS
    if ('ontouchstart' in window) {
        fixBody.on('focusin', 'input, textarea, select', function(event, element) {
            fixBody.addClassName('is-fixfixed');
        });

        fixBody.on('focusout', 'input, textarea, select', function(event, element) {
            fixBody.removeClassName('is-fixfixed');
        });
    }

    // fix focusout on cart item quantity on Android. can't touch update button without this
    fixBody.on('focusout', '.cart-item__quantity', function(event, element) {
        event.preventDefault();
        if (event.relatedTarget && event.relatedTarget.className == 'cart-item__update js-update-cart') {
            element.focus();
        }
    });

    // fix focusout on cart item quantity on Android. can't touch update button without this for FF
    fixBody.on('mousedown', '.cart-item__update', function(event, element) {
        event.preventDefault();
        $$('cart-item__quantity').first().focus();
    });
    fixBody.on('touchend', '.cart-item__update', function(event, element) {
        event.preventDefault();
        $$('.cart-item__update').first().click();
    });
});

var awMobile3 = awMobile3 || {};
awMobile3 = {};
awMobile3.instance = {};

/****************** Helpers ******************/

awMobile3.panelByUrl = function(url, panel) {
    if (window.location.href.search(url) !== -1) {
        awMobile3.panel.prototype.open(panel);
    }
};

awMobile3.loader = {
    _block: '.loader',
    show: function() {
        $$(this._block)[0].addClassName('is-visible');
    },
    hide: function() {
        $$(this._block)[0].removeClassName('is-visible');
    }
};

/****************** Panel ******************/

// Usage:

// awMobile3.panel.prototype.open("nav")
// awMobile3.panel.prototype.close("nav")
// awMobile3.panel.prototype.close(Element)

// var navigation = new awMobile3.panel('nav');
// navigation.open();
// navigation.close();

awMobile3.panel = Class.create({
    initialize: function(panelName) {
        this.panelName = panelName;
        this.observers();
        return this;
    },
    open: function(panelName) {
        if (!panelName) {
            panelName = this.panelName;
        }

        var panel = $$('.' + panelName)[0];

        panel.addClassName('is-open');

        // Fix page header overlaping over Configure and Buy panel
        $$('.wrapper')[0].addClassName('is-fixed-for-configure-on-ios');

        // Lock panel position to prevent browser ability to drag panel around with it's header
        var afterTransition = function(event) {
            panel.addClassName('is-steady-position');
            panel.addClassName('is-steady-time');

            this.stopObserving('webkitTransitionEnd', afterTransition);
            this.stopObserving('oTransitionEnd', afterTransition);
            this.stopObserving('MSTransitionEnd', afterTransition);
            this.stopObserving('transitionend', afterTransition);
        };

        panel.observe('webkitTransitionEnd', afterTransition);
        panel.observe('oTransitionEnd', afterTransition);
        panel.observe('MSTransitionEnd', afterTransition);
        panel.observe('transitionend', afterTransition);

        return this;
    },
    close: function(panelName) {
        var panel;

        if (typeof panelName === 'object') {
            panel = panelName;
        } else if (typeof panelName === 'string') {
            panel = $$('.' + panelName)[0];
        } else {
            panel = $$('.' + this.panelName)[0];
        }

        if (!panel || !$$('.wrapper')[0]) return this;

        // Fix page header overlaping over Configure and Buy panel
        $$('.wrapper')[0].removeClassName('is-fixed-for-configure-on-ios');

        // Chaining class remove. It's neccessery for animation. Each class need to be removed only after previous class removing take effect in browser.
        panel.removeClassName('is-steady-position');

        setTimeout(function() {
            panel.removeClassName('is-steady-time');
        }, 30);

        setTimeout(function() {
            panel.removeClassName('is-open');
        }, 30);

        return this;
    },
    observers: function() {
        var body = $$('body')[0];

        body.on('click', '.js-open-panel', function(event, element) {
            var panel = $(element).readAttribute('data-open-panel');

            this.open(panel);

            event.preventDefault();
        }.bind(this));

        body.on('click', '.panel__close', function(event, element) {
            var panel = $(element).up('.panel');
            this.close(panel);

            event.preventDefault();
        }.bind(this));
    }
});

Event.observe(document, 'dom:loaded', function(e) {
    awMobile3.instance.panels = new awMobile3.panel();
});

/****************** Panel Containers ******************/

awMobile3.panelContainers = Class.create({
    initialize: function() {
        this.target = null;
        this.currentContainer = null;
        this.targetContainer = null;
        this.panel = null;
        this.direction = 'forward';
        this.navigationPath = {};

        this.observers();

        return this;
    },
    animateTitle: function() {
        var self = this;
        var titleElement = $(this.panel).down('.panel__title');
        var titleOld = titleElement.innerHTML.stripTags();
        var titleNew = $(this.targetContainer).readAttribute('data-title');
        var titleOldElement = '<span class="panel__title-text panel__title-text--old">' + titleOld + '</span>';
        var titleNewElement = '<span class="panel__title-text panel__title-text--new">' + titleNew + '</span>';
        var afterAnimation = function(event) {
            titleElement.down('.panel__title-text--old').remove();
            titleElement.down('.panel__title-text--new').removeClassName('panel__title-text--new');
            titleElement.removeClassName('is-animated-' + self.direction);

            this.stopObserving('webkitAnimationEnd', afterAnimation);
            this.stopObserving('MSAnimationEnd', afterAnimation);
            this.stopObserving('oAnimationEnd', afterAnimation);
            this.stopObserving('animationend', afterAnimation);
        };

        titleElement.update(titleOldElement + titleNewElement);
        titleElement.addClassName('is-animated-' + self.direction);

        titleElement.observe('webkitAnimationEnd', afterAnimation);
        titleElement.observe('MSAnimationEnd', afterAnimation);
        titleElement.observe('oAnimationEnd', afterAnimation);
        titleElement.observe('animationend', afterAnimation);
    },
    updateBackButton: function() {
        if (this.direction === 'forward') {
            $(this.panel).down('.panel__back').addClassName('is-visible');
        }
        if (this.direction === 'backward' && this.target === 'main-container') {
            $(this.panel).down('.panel__back').removeClassName('is-visible');
        }
    },
    updateShadow: function() {
        if (this.direction === 'forward') {
            $(this.currentContainer).down('.panel__container-shadow').addClassName('is-visible');
        }
        if (this.direction === 'backward') {
            $(this.targetContainer).down('.panel__container-shadow').removeClassName('is-visible');
        }
    },
    showContainer: function() {
        $(this.targetContainer).addClassName('is-visible');
    },
    hideContainer: function() {
        $(this.currentContainer).removeClassName('is-visible');
    },
    changeContainer: function() {
        // анимация заголовка
        this.animateTitle();
        // показать Back
        this.updateBackButton();
        // показать тень у текущего контейнера
        this.updateShadow();
        // показать новый контейнер

        if (this.direction === 'forward') {
            this.showContainer();
        }
        if (this.direction === 'backward') {
            this.hideContainer();
        }
    },
    updatePath: function() {
        var panelName = this.panel.className;
        this.navigationPath[panelName] = this.navigationPath[panelName] || ['main-container'];

        if (this.direction === 'forward') {
            this.navigationPath[panelName].push(this.target);
            // console.log("Navigation Path", this.navigationPath[panelName]);
        }

        if (this.direction === 'backward') {
            this.navigationPath[panelName].pop();
            // console.log("Navigation Path", this.navigationPath[panelName]);
        }
    },
    removePath: function(panelName) {
        for (var path in this.navigationPath) {
            if (path.indexOf(panelName) > -1) {
                delete this.navigationPath[path];
                return;
            }
        }
    },
    observers: function() {
        var body = $$('body')[0];

        body.on('click', '[data-open-container]', function(event, element) {
            var target = $(element).readAttribute('data-open-container');
            this.target = target;
            this.panel = $(element).up('.panel');

            this.targetContainer = $(this.panel).down('[data-container="' + target + '"]');
            this.currentContainer = $(element).up('.panel__container');

            this.direction = 'forward';

            this.updatePath();
            this.changeContainer();

            event.preventDefault();
        }.bind(this));

        body.on('click', '.panel__back', function(event, element) {
            var panel = $(element).up('.panel');
            var navigationPath = this.navigationPath[panel.className];
            var current = navigationPath[navigationPath.length - 1];
            var target = navigationPath[navigationPath.length - 2];

            this.target = target;
            this.panel = panel;
            this.direction = 'backward';

            this.currentContainer = $(this.panel).down('[data-container="' + current + '"]');

            if (target === 'main-container') {
                this.targetContainer = $(this.panel).down('.panel__container');
            } else {
                this.targetContainer = $(this.panel).down('[data-container="' + target + '"]');
            }

            this.changeContainer();
            this.updatePath();

            event.preventDefault();
        }.bind(this));
    }
});

Event.observe(document, 'dom:loaded', function(e) {
    awMobile3.instance.panelContainers = new awMobile3.panelContainers();
});

/****************** Tabs ******************/

awMobile3.tabs = Class.create({
    initialize: function() {
        this.observers();
        return this;
    },
    switchLabels: function(element) {
        $(element).addClassName('is-current');

        $(element).siblings().each(function(el) {
            $(el).removeClassName('is-current');
        });
    },
    switchContainers: function(element) {
        var tabIndex = $$('.tabs__label').indexOf(element);

        $$('.tabs__tab').each(function(el, index) {
            el.removeClassName('is-visible');

            if (index === tabIndex) {
                el.addClassName('is-visible');
            }
        });
    },
    observers: function() {
        $$('body')[0].on('click', '.tabs__label:not(.is-current)', function(event, element) {
            this.switchLabels(element);
            this.switchContainers(element);
        }.bind(this));
    }
});

Event.observe(document, 'dom:loaded', function(e) {
    awMobile3.instance.tabs = new awMobile3.tabs();
});

/****************** Hide header on scroll ******************/

var isHideHeaderOnScroll = false;

if (isHideHeaderOnScroll) {
    Event.observe(document, 'dom:loaded', function(e) {
        (function(document, window, index) {
            'use strict';

            var elSelector = '.header__inner';
            var elClassHidden = 'is-hidden';
            var throttleTimeout = 500;
            var element = document.querySelector(elSelector);

            if (!element) { return true; }

            var dHeight = 0;
            var wHeight = 0;
            var wScrollCurrent = 0;
            var wScrollBefore = 0;
            var wScrollDiff = 0;

            var hasElementClass = function(element, className) {
                return element.classList ? element.classList.contains(className) : new RegExp('(^| )' + className + '( |$)', 'gi').test(element.className);
            };
            var addElementClass = function(element, className) {
                element.classList ? element.classList.add(className) : element.className += ' ' + className;
            };
            var removeElementClass = function(element, className) {
                element.classList ? element.classList.remove(className) : element.className = element.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
            };

            var throttle = function(delay, fn) {
                var last;
                var deferTimer;
                return function() {
                    var context = this;
                    var args = arguments;
                    var now = Number(new Date);
                    if (last && now < last + delay) { // jscs:ignore disallowSpacesInCallExpression
                        clearTimeout(deferTimer);
                        deferTimer = setTimeout(function() {
                            last = now;
                            fn.apply(context, args);
                        }, delay);
                    } else {
                        last = now;
                        fn.apply(context, args);
                    }
                };
            };

            window.addEventListener('scroll', throttle(throttleTimeout, function() {
                dHeight = document.body.offsetHeight;
                wHeight = window.innerHeight;
                wScrollCurrent = window.pageYOffset;
                wScrollDiff = wScrollBefore - wScrollCurrent;

                if (wScrollCurrent <= 0) {
                    // scrolled to the very top; element sticks to the top
                    removeElementClass(element, elClassHidden);
                } else if (wScrollDiff > 0 && hasElementClass(element, elClassHidden)) {
                    // scrolled up; element slides in
                    removeElementClass(element, elClassHidden);
                } else if (wScrollDiff < 0) {
                    // scrolled down
                    if (wScrollCurrent + wHeight >= dHeight && hasElementClass(element, elClassHidden)) {
                        // scrolled to the very bottom; element slides in
                        removeElementClass(element, elClassHidden);
                    } else {
                        // scrolled down; element slides out
                        addElementClass(element, elClassHidden);
                    }
                }

                wScrollBefore = wScrollCurrent;
            }));
        }(document, window, 0));
    });
}

/****************** Pager ******************/
awMobile3.pager = Class.create({
    initialize: function(buttonId, currentPage, lastPageNum) {
        this._currentPage = currentPage;
        this._lastPageNum = lastPageNum;
        this._buttonId = buttonId;
        this.containerId = null;
        this.tableId = null;
        this._pagerRequestVar = 'p';
        this._observerRequestVar = 'aw_mobile3';
        this.buttonWrapper = $(this._buttonId).up();

        if (this._currentPage !== this._lastPageNum) {
            this.buttonWrapper.addClassName('is-visible');
        }

        $(this._buttonId).observe('click', function() {
            this.showNext();
        }.bind(this));
    },
    reInit: function(currentPageNum, lastPageNum) {
        this._currentPage = currentPageNum;
        this._lastPageNum = lastPageNum;

        if (this._currentPage !== this._lastPageNum) {
            this.buttonWrapper.addClassName('is-visible');
            this.buttonWrapper.removeClassName('is-loading');
        } else {
            this.buttonWrapper.removeClassName('is-visible');
        }
    },
    showNext: function() {
        this.buttonWrapper.addClassName('is-loading');

        var url = window.location.href.split('?')[0];
        var options = {
            method: 'GET',
            parameters: this._getRequestParams(),
            onComplete: function(transport) {
                var json = transport.responseText.evalJSON();

                if ($(this.containerId) && json.content) {
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = json.content;

                    if (this.tableId) {
                        $(this.tableId).select('tbody').first().insert(tempDiv.select('#' + this.tableId + ' tbody').first().innerHTML);
                    } else {
                        $(this.containerId).insert(tempDiv.down().innerHTML);
                    }

                    json.content.evalScripts();
                }
            }.bind(this),
            onException: function() {
                this.buttonWrapper.removeClassName('is-loading');
            }.bind(this)
        };

        new Ajax.Request(url, options);
    },
    _getRequestParams: function() {
        var request = {};
        var params = window.location.href.split('?')[1];

        if (params) {
            var pairs = params.split('&');

            for (var i = 0; i < pairs.length; i++) {
                var pair = pairs[i].split('=');
                if (pair[0] == 'q') {
                    var searchFormVal = $$('.search__form input[name=q]').first().value;
                    if (searchFormVal.length) {
                        pair[1] = searchFormVal;
                    }
                }
                request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
            }
        }

        request[this._observerRequestVar] = true;
        request[this._pagerRequestVar] = this._currentPage + 1;

        return request;
    }
});

/****************** Customer Account ******************/

awMobile3.customerAccount = Class.create({
    initialize: function() {
        this.loginFormId = 'user_login_form';
        this.forgotFormId = 'user_forgotpassword_form';
        this.registerFormId = 'user_create_form';
        this.observers();
    },
    submitForm: function(form) {
        var validator = new Validation(form);

        if (validator.validate()) {
            $(form).submit();
        }
    },
    observeForm: function(form) {
        if ($(form)) {
            $(form).observe('submit', function(event) {
                this.submitForm(form);
                Event.stop(event);
            }.bind(this));
        }
    },
    observers: function() {
        this.observeForm(this.loginFormId);
        this.observeForm(this.forgotFormId);
        this.observeForm(this.registerFormId);

        awMobile3.panelByUrl('#account', 'customer-panel');

        $$('.js-delete-address').each(function(el) {
            el.observe('click', function(event) {
                if (!confirm(el.readAttribute('data-question'))) {
                    event.preventDefault();
                }
            });
        });
    }
});

Event.observe(document, 'dom:loaded', function(e) {
    awMobile3.instance.customerAccount = new awMobile3.customerAccount();
});

/****************** Sorting ******************/

awMobile3.sorting = Class.create({
    initialize: function() {
        this.sortingPanel = 'sorting';
        this.sortingForm = $$('.sorting__form')[0];
        this.observerRequestVar = 'aw_mobile3';
        this.containerId = 'product_list';
        this.renderSortingButton();
        this.observers();
    },
    getRequestParams: function(form) {
        var request = $(this.sortingForm).serialize(true);

        var params = window.location.href.split('?')[1];
        if (params) {
            var pairs = params.split('&');

            for (var i = 0; i < pairs.length; i++) {
                var pair = pairs[i].split('=');
                if (decodeURIComponent(pair[0]) == 'order' || decodeURIComponent(pair[0]) == 'dir') {
                    continue;
                }

                request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
            }
        }

        request[this.observerRequestVar] = true;

        return request;
    },
    getNewUrl: function() {
        var pairs = [];
        var requestParams = this.getRequestParams();

        delete requestParams[this.observerRequestVar];

        for (var key in requestParams) {
            if (requestParams.hasOwnProperty(key)) {
                pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(requestParams[key]));
            }
        }

        return window.location.href.split('?')[0] + '?' + pairs.join('&');
    },
    updateProductList: function(response) {
        if (response.content) {
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = response.content;
            $(this.containerId).innerHTML = tempDiv.down().innerHTML;
            response.content.evalScripts();
        }
    },
    renderSortingButton: function() {
        // show panel button only if there are more then one item
        var productsCount = $$('.products .product-card').length;

        if(typeof($$('.page-header__action--sort')[0]) != 'undefined') {
            if (productsCount && productsCount > 1) {
                $$('.page-header__action--sort')[0].removeClassName('is-hidden');
            } else {
                $$('.page-header__action--sort')[0].addClassName('is-hidden');
            }
        }
    },
    observers: function() {
        if ($(this.sortingForm)) {
            $(this.sortingForm).observe('submit', function(event) {
                Event.stop(event);

                awMobile3.panel.prototype.close(this.sortingPanel);
                awMobile3.loader.show();

                var url = window.location.href.split('?')[0];
                var options = {
                    method: 'GET',
                    parameters: this.getRequestParams('.sorting__form'),
                    onComplete: function(transport) {
                        var json = transport.responseText.evalJSON();

                        this.updateProductList(json);

                        var newUrl = this.getNewUrl();
                        window.history.pushState({}, 'New URL: ' + newUrl, newUrl);

                        awMobile3.loader.hide();
                    }.bind(this),
                    onException: function() {
                        awMobile3.loader.hide();
                    }.bind(this)
                };

                new Ajax.Request(url, options);
            }.bind(this));
        }
    }
});

Event.observe(document, 'dom:loaded', function(e) {
    awMobile3.instance.sorting = new awMobile3.sorting();
});

/****************** Filter ******************/

awMobile3.filter = Class.create({
    initialize: function() {
        this.filterPanel = 'filter';
        this.filterFormClass = '.filter__form';
        this.observerRequestVar = 'aw_mobile3';
        this.containerId = 'product_list';
        this.clearAllFilters = false;
        this.clearFilterName = null;
        this.fixForDisabledFilter();
        this.observers();
    },
    enableApplyButton: function() {
        $$('.js-apply-filter')[0].writeAttribute('disabled', false);
    },
    disableApplyButton: function() {
        $$('.js-apply-filter')[0].writeAttribute('disabled', true);
    },
    updateFilterText: function(element, text) {
        var filterName = $(element).readAttribute('data-filter-title');
        var filterTextElement = $$('.filter__selected-option[data-filter-title="' + filterName + '"]')[0];

        if (text === 'empty') {
            filterTextElement.innerHTML = '';
        } else {
            var optionText = $(element).down('.filter__option-text').innerHTML.strip();

            filterTextElement.innerHTML = filterTextElement.readAttribute('data-default-title') + optionText;
        }
    },
    clearFilter: function(name) {
        $$(this.filterFormClass + ' [name="' + name + '"]').each(function(el) {
            el.checked = false;
        });

        if (!this.isAnyFilterNewlySelected()) {
            this.disableApplyButton();
        }
    },
    applyFilter: function() {
        awMobile3.loader.show();

        var url = window.location.href.split('?')[0];
        var options = {
            method: 'GET',
            parameters: this.getRequestParams(),
            onComplete: function(transport) {
                if (!transport.responseText.isJSON()) {
                    document.location.href = url;
                    return;
                }

                var json = transport.responseText.evalJSON();

                $$('.wrapper')[0].scrollTop = 0; // scroll to top
                this.updateProductList(json.content);
                this.updateFilterPanel(json.layer);
                this.updateUrl();
                awMobile3.loader.hide();

                this.clearAllFilters = false;
                this.clearFilterName = null;
            }.bind(this),
            onException: function() {
                awMobile3.loader.hide();
            }.bind(this)
        };

        new Ajax.Request(url, options);
    },
    selectOption: function() {
        this.enableApplyButton();
    },
    updateProductList: function(content) {
        if (content) {
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            $(this.containerId).innerHTML = tempDiv.down('#' + this.containerId).innerHTML;
            content.evalScripts();
            awMobile3.sorting.prototype.renderSortingButton();
        }

        return true;
    },
    updateFilterPanel: function(panel) {
        var self = this;
        var filterPanel = $$('.' + this.filterPanel)[0];
        var afterTransition = function() {
            filterPanel.remove();
            awMobile3.instance.panelContainers.removePath(self.filterPanel);

            this.stopObserving('webkitTransitionEnd', afterTransition);
            this.stopObserving('oTransitionEnd', afterTransition);
            this.stopObserving('MSTransitionEnd', afterTransition);
            this.stopObserving('transitionend', afterTransition);
        };

        filterPanel.observe('webkitTransitionEnd', afterTransition);
        filterPanel.observe('oTransitionEnd', afterTransition);
        filterPanel.observe('MSTransitionEnd', afterTransition);
        filterPanel.observe('transitionend', afterTransition);

        awMobile3.panel.prototype.close(this.filterPanel);

        $$('body')[0].insert({
            bottom: panel
        });
    },
    updateUrl: function() {
        var url = this.getNewUrl();
        window.history.pushState({}, 'New URL: ' + url, url);
    },
    isAnyFilterNewlySelected: function() {
        if ($$(this.filterFormClass)[0].serialize()) {
            return true;
        }

        return false;
    },
    getRequestParams: function() {
        var request = $$(this.filterFormClass)[0].serialize(true);
        var params = window.location.href.split('?')[1];

        if (params) {
            var pairs = params.split('&');

            for (var i = 0; i < pairs.length; i++) {
                var pair = pairs[i].split('=');

                request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1].replace(/\+/g, '%20'));
            }
        }

        if (this.clearFilterName) {
            delete request[this.clearFilterName];
        }

        if (this.clearAllFilters) {
            var allParameters = this.getAllParametersNames();

            allParameters.forEach(function(name) {
                delete request[name];
            });
        }

        request[this.observerRequestVar] = true;

        return request;
    },
    getNewUrl: function() {
        var pairs = [];
        var requestParams = this.getRequestParams();

        delete requestParams[this.observerRequestVar];

        for (var key in requestParams) {
            if (requestParams.hasOwnProperty(key)) {
                pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(requestParams[key]));
            }
        }

        if (pairs.length) {
            return window.location.href.split('?')[0] + '?' + pairs.join('&');
        }

        return window.location.href.split('?')[0];
    },
    getAllParametersNames: function() {
        var parameters = [];

        $$(this.filterFormClass + ' [name]').each(function(el) {
            var name = el.readAttribute('name');

            if (parameters.indexOf(name) < 0) {
                parameters.push(name);
            }
        });

        $$(this.filterFormClass + ' [data-filter-name]').each(function(el) {
            var name = el.readAttribute('data-filter-name');

            if (parameters.indexOf(name) < 0) {
                parameters.push(name);
            }
        });

        return parameters;
    },
    fixForDisabledFilter: function() {
        // if there is no filter panel opening button should be removed
        if ($$('.js-open-filter-button-wrapper').length && !$$('.filter').length) {
            $$('.js-open-filter-button-wrapper')[0].remove();
        }
    },
    observers: function() {
        var body = $$('body')[0];

        body.on('click', '.js-clear-current-filter', function(event, element) {
            var name = $(element).readAttribute('data-filter-name');

            this.clearFilter(name);
            this.updateFilterText(element, 'empty');

            event.preventDefault();
        }.bind(this));

        body.on('click', '.js-filter-option-click', function(event, element) {
            this.enableApplyButton();
            this.updateFilterText(element);
        }.bind(this));

        body.on('click', '.js-apply-filter', function(event, element) {
            this.applyFilter();

            event.preventDefault();
        }.bind(this));

        body.on('click', '.js-remove-applied-filter', function(event, element) {
            this.clearFilterName = $(element).readAttribute('data-filter-name');
            this.applyFilter();

            event.preventDefault();
        }.bind(this));

        body.on('click', '.js-clear-all-filters', function(event, element) {
            this.clearAllFilters = true;
            this.applyFilter();

            event.preventDefault();
        }.bind(this));
    }
});

Event.observe(document, 'dom:loaded', function(e) {
    awMobile3.instance.filter = new awMobile3.filter();
});

/****************** Cart ******************/

awMobile3.cart = Class.create(VarienForm, {
    productOptions: 'add-to-cart-options',
    updateCartId: 'update_cart_form',
    requestUpdate: function(url, parameters, scroll) {
        var options = {
            method: 'post',
            parameters: parameters,
            onComplete: function(transport) {
                if (!transport.responseText.isJSON()) {
                    document.location.href = url;
                    return;
                }

                var json = transport.responseText.evalJSON();

                if (json.redirect_to) {
                    document.location.href = json.redirect_to;
                    return;
                }

                if (json.count >= 0) {
                    this.updateIcon(json.count);
                }

                if (json.message) {
                    this.updateMessages(json.message);
                    if (scroll) {
                        $$('.wrapper')[0].scrollTop = 0; // scroll to top to see the message
                    }
                } else {
                    this.updateMessages('');
                }

                if (json.cart) {
                    this.updateCart(json.cart);
                }

                if (json.discount) {
                    this.updateDiscountPanel(json.discount);
                }

                awMobile3.loader.hide();
            }.bind(this)
        };

        awMobile3.loader.show();

        new Ajax.Request(url, options);
    },
    sendRequest: function() {
        if (!this.validator.validate()) {
            return false;
        }

        awMobile3.panel.prototype.close(this.productOptions);
        this.requestUpdate(this.form.action, this.form.serialize(true), true);

        return true;
    },
    updateCart: function(content) {
        $$('.cart__content')[0].innerHTML = content;

        return true;
    },
    updateIcon: function(count) {
        // HTML code should be same as in /page/html/header.phtml
        var content = '<svg class="site-header__action-icon site-header__action-icon--cart svg-icon"><use xlink:href="#icon-cart--empty" /></svg>';

        if (count > 0) {
            content = '<svg class="site-header__action-icon site-header__action-icon--cart svg-icon"><use xlink:href="#icon-cart" /></svg>' +
            '<span class="site-header__cart-items">' + count + '</span>';
        }

        $$('.cart-count')[0].innerHTML = content;

        return true;
    },
    updateMessages: function(content) {
        if ($('messages_product_view')) {
            $('messages_product_view').innerHTML = content;
        }

        return true;
    },
    updateDiscountPanel: function(panel) {
        $$('.discount')[0].remove();

        $$('body')[0].insert({
            bottom: panel
        });

        awMobile3.instance.discount = new awMobile3.cart('discount-coupon-form');
        awMobile3.instance.discount.observersDiscount();
    },
    deleteItem: function(url) {
        this.requestUpdate(url);

        return true;
    },
    applyDiscount: function(apply) {
        if (apply) {
            $('coupon_code').addClassName('required-entry');
            $('remove-coupon').value = '0';
        } else {
            $('coupon_code').removeClassName('required-entry');
            $('remove-coupon').value = '1';
        }

        if (!this.validator.validate()) {
            return false;
        }

        $$('.cart__container')[0].scrollTop = 0;
        awMobile3.panel.prototype.close('discount');
        this.requestUpdate($('discount-coupon-form').action, $('discount-coupon-form').serialize(true));
    },
    observersAddToCart: function() {
        $$('body')[0].on('submit', '.product__buy-form', function(event, element) {
            // if on the form is a field type=file is not use Ajax
            if ($$('#file_field_exist').length > 0) {
                if (!this.validator.validate()) {
                    return false;
                }
            } else {
                this.sendRequest();
                event.preventDefault();
            }
        }.bind(this));
    },
    observersCart: function() {
        var body = $$('body')[0];

        body.on('click', '.js-delete-product-from-cart', function(event, element) {
            var url = $(element).readAttribute('data-delete-url');

            this.deleteItem(url);

            event.preventDefault();
        }.bind(this));

        body.on('submit', '.cart__items', function(event, element) {
            this.requestUpdate($(this.updateCartId).action, $(this.updateCartId).serialize(true));

            event.preventDefault();
        }.bind(this));
    },
    observersDiscount: function() {
        var body = $$('body')[0];

        body.on('submit', '#discount-coupon-form', function(event, element) {
            this.applyDiscount(true);

            event.preventDefault();
        }.bind(this));

        body.on('click', '.js-remove-discount', function(event, element) {
            this.applyDiscount(false);

            event.preventDefault();
        }.bind(this));
    }
});

Event.observe(document, 'dom:loaded', function(e) {
    awMobile3.instance.addToCart = new awMobile3.cart('product_addtocart_form');
    awMobile3.instance.addToCart.observersAddToCart();

    awMobile3.instance.discount = new awMobile3.cart('discount-coupon-form');
    awMobile3.instance.discount.observersDiscount();

    awMobile3.instance.cart = new awMobile3.cart('update_cart_form');
    awMobile3.instance.cart.observersCart();
    awMobile3.panelByUrl('#cart', 'cart');
});

/****************** Gallery ******************/

awMobile3.gallery = Class.create({
    initialize: function() {
        this.observers();
    },
    getItems: function(gallery) {
        var items = [];

        gallery.select('.gallery__link').each(function(link) {
            var href = link.href;
            var size = link.readAttribute('data-size').split('x');
            var width = size[0];
            var height = size[1];

            items.push({
                src: href,
                w: width,
                h: height
            });
        });

        return items;
    },
    observers: function() {
        var gallery = $$('.gallery')[0];

        if (gallery) {
            var items = this.getItems(gallery);
            var pswp = $$('.pswp')[0];
            var options = {
                history: false,
                getThumbBoundsFn: function(index) {
                    var thumbnail = gallery.select('.gallery__image')[index];
                    var pageYScroll = window.pageYOffset || document.documentElement.scrollTop;
                    var rect = thumbnail.getBoundingClientRect();

                    return {
                        x: rect.left,
                        y: rect.top + pageYScroll,
                        w: rect.width
                    };
                }
            };

            gallery.on('click', '.gallery__item', function(event, element) {
                var index = element.previousSiblings().size();
                options.index = index;

                var lightBox = new PhotoSwipe(pswp, PhotoSwipeUI_Default, items, options); // jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
                lightBox.init();

                event.preventDefault();
            });
        }
    }
});

Event.observe(document, 'dom:loaded', function(e) {
    awMobile3.instance.gallery = new awMobile3.gallery();
});

/****************** Product Quantity ******************/

awMobile3.productQuantity = Class.create({
    initialize: function() {
        this.valueElement = $$('.js-product-quantity-value')[0];
        this.observers();
    },
    increase: function() {
        this.valueElement.value = parseInt(this.valueElement.value) + 1;
        $$('.wrapper')[0].setStyle({'float': 'none'});
    },
    decrease: function() {
        this.valueElement.value = parseInt(this.valueElement.value) - 1;
        this.checkValue();
        $$('.wrapper')[0].setStyle({'float': 'none'});
    },
    checkValue: function() {
        var minimalQty = parseInt(this.valueElement.readAttribute('data-minimal-qty'));

        if (this.valueElement.value < minimalQty) {
            this.valueElement.value = minimalQty;
        }
    },
    observers: function() {
        if ($$('.js-product-quantity-decrease')[0]) {
            $$('.js-product-quantity-decrease')[0].observe('click', function(event) {
                this.decrease();

                event.preventDefault();
            }.bind(this));
        }

        if ($$('.js-product-quantity-increase')[0]) {
            $$('.js-product-quantity-increase')[0].observe('click', function(event) {
                this.increase();

                event.preventDefault();
            }.bind(this));
        }

        if (this.valueElement) {
            this.valueElement.observe('blur', function(event) {
                this.checkValue();

                event.preventDefault();
            }.bind(this));
        }
    }
});

Event.observe(document, 'dom:loaded', function(e) {
    awMobile3.instance.productQuantity = new awMobile3.productQuantity();
});
