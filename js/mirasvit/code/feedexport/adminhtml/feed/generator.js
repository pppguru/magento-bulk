var FeedGenerator = {

    messageTimeout: null,

    generate: function(url, stateUrl, id, status)
    {
        var self      = this;
        this.url      = url;
        this.feedId   = id;
        this.stateUrl = stateUrl;
        this.loader   = new FeedGeneratorLoader(this.stateUrl, this.feedId);

        this.request = new Ajax.Request(this.url, {
            method     : 'POST',
            parameters : {id: this.feedId},
            loaderArea : false,

            onCreate: function() {
                self.loader.start();
            },

            onComplete: function(response) {
                self.loader.finish();

                if (response.responseText.isJSON()) {
                    var json = response.responseText.evalJSON();

                    if (json.success) {
                        if (json.status == 'ready') {
                            self.addMessage('success', json.message);
                        } else if (json.status != 'error') {
                            self.generate(self.continueUrl(url), stateUrl, id, json.status);
                        } else if (json.status == 'error') {
                            self.addMessage('error', json.message);
                        }
                    } else {
                        self.addMessage('error', json.message);
                    }
                } else {
                    self.addMessage('error', response.responseText);
                    self.generate(self.continueUrl(url), stateUrl, id, 'processing');
                }
            }
        });
    },

    addMessage: function(type, text)
    {
        var self = this;

        clearTimeout(self.messageTimeout);
        $('messages').innerHTML = '';
        $('messages').show();

        if (text !== '') {
            $('messages').insert('<ul class="messages"><li class="' + type + '-msg"><ul><li><span>' + text + '</span></li></ul></li></ul>');
            self.messageTimeout = setTimeout(function() {
                $('messages').fade({duration: 3.0, from: 0, to: 1});
            }, 10000);

        }
    },

    continueUrl: function(url)
    {
        url = url.replace('mode/new', 'mode/continue');

        return url;
    },

    abort: function()
    {
        console.log('abort');
        if (this.request) {
            this.loader.finish();
            this.request.transport.abort();
            this.request = null;
        }
    }
};