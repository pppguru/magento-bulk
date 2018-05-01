var AWHDUDepartment = Class.create({
    initialize:function () {
        this.selectUseNotifications = $('notify');
        this.inputEmail = $('contact');
        this.starSpan = this.inputEmail.up().up().select('span.required').first();

        if (this.selectUseNotifications && this.inputEmail) {
            this.selectUseNotifications.observe('change', this.checkUseNotifications.bind(this));
            this.checkUseNotifications();
        }
    },

    checkUseNotifications:function () {
        if (parseInt(this.selectUseNotifications.value)) {
            this.makeEmailRequired();
        } else {
            this.unrequireEmailField();
        }
    },

    makeEmailRequired:function () {
        this.inputEmail.addClassName('required-entry');
        this.inputEmail.addClassName('validate-uniq-email');
        if (this.starSpan) {
            this.starSpan.show();
        }
    },

    unrequireEmailField:function () {
        this.inputEmail.removeClassName('required-entry');
        this.inputEmail.removeClassName('validate-uniq-email');
        if (this.starSpan) {
            this.starSpan.hide();
        }
    }
});

Event.observe(document, "dom:loaded", function(e) {
    new AWHDUDepartment();
});
