var isEE = (navigator.userAgent.indexOf("MSIE") != false);
var AWAdvancedReportsColumns = Class.create();
AWAdvancedReportsColumns.prototype = {
    initialize: function () {
        this.columns = new Array();
        this.activeOverlayId = null;
        this.focusedElementId = null;
        document.observe("dom:loaded", (function (event) {

            /* Mouse out column */
            $$('.aw-column').each((function (element) {
                element.observe('mouseout', (function (event) {
                    this.hideOverlay();
                }).bind(this));
            }).bind(this));

            /* Mouse over column */
            $$('.aw-column').each((function (element) {
                element.observe('mouseover', (function (event) {
                    var colId = this.findColumnId(event.target);
                    if (colId) {
                        if (this.focusedElementId) {
                            if (this.focusedElementId == ('aw-column-header-' + colId)) {
                                return false;
                            }
                        }
                        this.showOverlay(colId);
                    }
                }).bind(this));
            }).bind(this));

            /* Mouse move column */
            $$('.aw-column').each((function (element) {
                element.observe('mousemove', (function (event) {
                    var colId = this.findColumnId(event.target);
                    if (colId) {
                        if ($('aw-column-header-' + colId)) {
                            if (this.isAtInput(event)) {
                                $('aw-column-header-' + colId).addClassName('over');
                            } else {
                                $('aw-column-header-' + colId).removeClassName('over');
                            }
                        }
                    }
                }).bind(this));
            }).bind(this));

            /* Mouse over input text */
            $$('input.text').each((function (element) {
                element.observe('mouseover', (function (event) {
                    var colId = this.findColumnId(event.target);
                    if (colId) {
                        this.hideOverlay();
                    }
                }).bind(this));
            }).bind(this));

            /* Click overlay */
            $$('.aw-column-overlay').each((function (element) {
                element.observe('click', (function (event) {
                    var colId = this.findColumnId(event.target);
                    if (colId) {
                        if (this.isAtInput(event)) {
                            this.hideOverlay();
                            if ($('aw-column-header-' + colId)) {
                                this.hideOverlay();
                                $('aw-column-header-' + colId).focus();
                                this.focusedElementId = 'aw-column-header-' + colId;
                            }
                        } else {
                            this.checkBox(this.findColumnId(event.target));
                        }
                    }
                }).bind(this));
            }).bind(this));

            /* Input text blur */
            $$('input.text').each((function (element) {
                element.observe('blur', (function (event) {
                    this.focusedElementId = null;
                }).bind(this));
            }).bind(this));

            /* Input text change */
            $$('input.text').each((function (element) {
                element.observe('change', (function (event) {
                    this.changeText(this.findColumnId(event.target), event.target.value);
                }).bind(this));
            }).bind(this));

        }).bind(this));
    },
    findColumnId: function (element) {
        if (typeof(element) == 'object') {
            var el = element;
            while (!$(el).hasClassName('aw-column')) {
                el = el.parentNode;
            }
            return el.id;
        }
    },
    showOverlay: function (id) {
        var ovId = 'overlay-' + id;
        if (this.activeOverlayId) {

            if (this.activeOverlayId != ovId) {
                if ($(this.activeOverlayId)) {
                    $(this.activeOverlayId).removeClassName('show');
                }
                this.activeOverlayId = ovId;
            }
        }
        this.activeOverlayId = ovId;
        if ($(ovId)) {
            if (!$(ovId).hasClassName('show')) {
                $(ovId).addClassName('show');
            }
        }
        if ($('aw-header-' + id)) {
            $('aw-header-' + id).addClassName('over');
        }
    },
    hideOverlay: function () {
        $$('.aw-column-overlay.show').each(function (element) {
            $(element).removeClassName('show');
        });
        $$('.text-container input.text.over').each(function (element) {
            $(element).removeClassName('over');
        });
        $$('.aw-header.over').each(function (element) {
            $(element).removeClassName('over');
        });
        this.activeOverlayId = null;
    },
    isAtInput: function (event) {
        if (isIE) {
            var x = event.offsetX;
            var y = event.offsetY;
        } else {
            var x = event.layerX;
            var y = event.layerY;
        }
        return  ((y > 43) && (y < 55));
    },
    changeText: function (id, value) {
        var column = this.columns[id];
        if (typeof(column) != 'undefined') {
            column.custom_header = value;
            this.renderColumns();
        }
    },
    checkBox: function (id) {
        var column = this.columns[id];
        if (typeof(column) != 'undefined') {
            column.checked = column.checked ? false : true;
            this.renderColumns();
        }
    },
    renderColumns: function () {
        var arr = new Array();
        for (id in this.columns) {
            var column = this.columns[id];
            if (typeof(column) != 'undefined') {
                if (id == column.column_id) {
                    if ($('aw-column-header-' + id)) {
                        $('aw-column-header-' + id).value = column.custom_header;
                    }
                    if ($('aw-column-checkbox-' + id)) {
                        $('aw-column-checkbox-' + id).checked = column.checked;
                    }
                }
                arr.push(column);
            }
        }

        if ($('custom_columns')) {
            $('custom_columns').value = Object.toJSON(arr);
        }
    },
    registerColumn: function (id, customHeader, checked) {
        this.columns[id] = {
            column_id: id,
            custom_header: customHeader,
            checked: checked
        };
        return this;
    }
}
