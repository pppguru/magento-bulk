var AWLIB = AWLIB || {};
AWLIB.FileUploader = Class.create({
    initialize: function(input, options) {
        this.input = input;
        this.options = options;
        this._stopObserverFnList = [];

        this.isBrowserSupportFileAPI = !Object.isUndefined(window.File) && !Object.isUndefined(window.FileReader)
            && !Object.isUndefined(window.FileList)
        ;
        this.startObservers();
    },

    startObservers: function() {
        var me = this;
        var fn = this.onFileSelect.bind(this);
        me.input.observe('change', fn);
        me._stopObserverFnList.push(function(){
            me.input.stopObserving('change', fn);
        });
    },

    stopObservers: function() {
        this._stopObserverFnList.each(function(fn){
            fn();
        });
    },

    onFileSelect: function(e) {
        if (!this.isBrowserSupportFileAPI) {
            return this._onFileSelectForOldBrowser(e);
        }
        return this._onFileSelect(e);
    },

    _onFileSelect: function(e) {
        var me = this;
        if (Object.isUndefined(me.list)) {
            me.list = new Element('ul');
            if (me.options.fileListClassName) {
                me.list.addClassName(me.options.fileListClassName);
            }
            me.input.up().insert(me.list, {'after':me.input});
        }
        var files = e.target.files;
        me.list.update('');
        Object.keys(files).each(function(index){
            if (!isFinite(index)) {
                return;
            }
            var file = files[index];
            var li = new Element('li');
            me.list.appendChild(li);
            if (!me._isFileSizeAllow(file.size)) {
                li.addClassName(me.options.errorMsgClassName);
                li.update(me.options.unexpectedFileSizeMessage(file.name));
                return;
            }
            if (!me._isFileMimeTypeAvailable(file.type)) {
                li.addClassName(me.options.errorMsgClassName);
                li.update(me.options.unexpectedFileExtensionMessage(file.name));
                return;
            }

            var titleSpan = new Element('span');
            titleSpan.update(
                '<label title="' + file.name + '">' +
                    '<nobr>' +
                    '<input type="checkbox" name="' + (me.options.fileListElName + '[' + index + ']') + '" checked>' +
                    file.name +
                    '</nobr>' +
                '</label>'
            );
            li.appendChild(titleSpan);
            if (file.type.match('image.*')) {
                var image = new Element('img');
                var reader = new FileReader();
                reader.onload = function(e) {
                    image.setAttribute('src', e.target.result);
                    image.setAttribute('title', file.name);
                    li.appendChild(image);
                };
                reader.readAsDataURL(file);
            }
        });
    },

    _onFileSelectForOldBrowser: function(e) {
        var newInput = this._createNewFileInput();
        this.input.up().insert(newInput, {'after':this.input});
        this.stopObservers();
        this.input = newInput;
        this.startObservers();
    },

    _createNewFileInput: function() {
        var fileInput = new Element('input');
        var me = this;
        Object.keys(this.input.attributes).each(function(index){
            var attributeName = me.input.attributes.item(index).nodeName;
            fileInput.setAttribute(attributeName, me.input.getAttribute(attributeName));
        });
        return fileInput;
    },

    _isFileMimeTypeAvailable: function(mimeType) {
        if (Object.isUndefined(this.options.availableFileExtensionList)) {
            return true;
        }
        var listIsNotNull = this.options.availableFileExtensionList.all(function(element){
            if (!element || !element.length) {
                return false;
            }
            return true;
        });
        if (!listIsNotNull) {
            return true;
        }

        var extensionName = mimeType.replace(/[^\/]+\//, '');
        return (this.options.availableFileExtensionList.indexOf(extensionName) != -1);
    },

    _isFileSizeAllow: function(size) {
        if (!this.options.maxFileSizeInBytes) {
            return true;
        }
        return size <= this.options.maxFileSizeInBytes;
    }

});