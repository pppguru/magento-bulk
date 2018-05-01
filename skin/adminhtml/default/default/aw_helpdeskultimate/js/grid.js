var AWHDUGrid = Class.create({
    initialize:function(name) {
        window[name] = this;
        document.observe('dom:loaded', this.init.bind(this));
    },

    init:function() {
        this.grid = $$('.grid table').first();
        if(this.grid != 'undefined') {
            this.gridCells = $$('#' + this.grid.identify() + ' tbody>tr>td');
            this.processCells();
        }
    },

    processCells: function() {
        this.gridCells.each(function(gridCell) {
            var nameAttr = this.detectNameInClass(gridCell.readAttribute('class'));
            if(nameAttr) {
                gridCell.writeAttribute('name', nameAttr);
            }
        }.bind(this));
    },

    detectNameInClass: function(classNames) {
        var classes = classNames.split(' ');
        var name = null;
        classes.each(function(className) {
            if(className.indexOf('aw-hduat-') == 0) {
                name = className.substring(9);
            }
        });
        return name;
    }
});

new AWHDUGrid('awhdugrid');
