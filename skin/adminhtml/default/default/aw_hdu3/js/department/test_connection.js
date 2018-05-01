var awHDU3GatewayTestConnection = {
    run: function(url, defaultErrorMsg) {
        var button = $('gateway_test_connection');
        var form = button.up('form');
        var fields = Form.serialize(form, true);
        var params = {};
        Object.keys(fields).each(function(key){
            if (key.indexOf('gateway') === 0) {
                params[key] = fields[key];
            }
        });
        var me = this;
        new Ajax.Request(url, {
            parameters: params,
            method: 'POST',
            onComplete: function (transport) {
                if (!transport.responseText.isJSON()) {
                    me.showError(defaultErrorMsg);
                    return;
                }
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        me.showSuccess(response.msg);
                    } else {
                        me.showError(response.msg);
                    }
                } catch (e) {
                    me.showError(defaultErrorMsg);
                }
            }
        });
    },

    showError: function(msg) {
        this.showMsg(msg, {
            'color': 'red',
            'fontWeight': 'bold'
        });
    },
    showSuccess: function(msg) {
        this.showMsg(msg, {
            'color': 'green',
            'fontWeight': 'bold'
        });
    },
    showMsg: function(msg, style) {
        var currentMsgEl = $('test_connection_msg');
        if (currentMsgEl) {
            currentMsgEl.remove();
        }
        var button = $('gateway_test_connection');
        var msgEl = new Element('div');
        msgEl.setAttribute('id', 'test_connection_msg');
        msgEl.update(msg).setStyle(style);
        button.insert({'after': msgEl});
    }
};