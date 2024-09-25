/**
 *
 * @module     local_challenge/challenge
 * @class      challenge
 * @package    local_challenge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/templates', 'core/fragment', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/notification'], 
        function ($, Ajax, Templates, Fragment, Str, ModalFactory, ModalEvents, Notification){
    var challenge = function(args){
    	this.args = args;
    	var self = this;
    	var head = Str.get_string(args.type, 'local_challenge');
        var strings = Str.get_strings([
            {
                key: args.type,
                component: 'local_challenge'
            },
            {
                key: 'cancel',
                component: 'moodle'
            }
            ]);
        return strings.then(function(string) {
    	 // head.then(function(title) {
            // Create the modal.
            return ModalFactory.create({
            type: ModalFactory.types.DEFAULT,
            title: string[0],
            body: this.getBody(),
            footer: this.getFooter(string),
            });
        }.bind(this)).then(function(modal) {
            // Keep a reference to the modal.
            this.modal = modal;

            // Forms are big, we want a big modal.
            this.modal.setLarge(); 

            this.modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
            }.bind(this));

            // We also catch the form submit event and use it to submit the form with ajax.
            this.modal.getFooter().find('[data-action="save"]').on('click', this.submitForm.bind(this));

            this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                modal.hide();// added for hiding the modal popup #required!
                modal.destroy();
            }.bind(this));

            this.modal.getRoot().on('submit', 'form', function(form) {
                self.submitFormAjax(form, self.args);
            });
            this.modal.show();
            // this.modal.getRoot().animate({"right":"0%"}, 500);

            return this.modal;
        }.bind(this));
    }
    challenge.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        this.args.jsonformdata = JSON.stringify(formdata);
        return Fragment.loadFragment('local_challenge', 'challenge_module', this.args.contextid, this.args);
    };
    challenge.prototype.getFooter = function(string) {

        $footer = '<button type="button" class="btn btn-primary" data-action="save">'+string[0]+'</button>&nbsp;';
        $footer += '<button type="button" class="btn btn-secondary" data-action="cancel">'+string[1]+'</button>';
        return $footer;
    };
    challenge.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };
    challenge.prototype.submitFormAjax = function(e ,args) {
        // We don't want to do a real form submission.
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        // var methodname = args.plugintype + '_' + args.pluginname + '_submit_create_user_form';
        var methodname = 'local_challenge_post_challenge';
        var params = {};
        params.contextid = this.args.contextid;
        params.jsonformdata = JSON.stringify(formData);

        var promise = Ajax.call([{
            methodname: methodname,
            args: params,
            done: this.handleFormSubmissionResponse.bind(this, formData),
            fail: this.handleFormSubmissionFailure.bind(this, formData)
        }]);
    };
    challenge.prototype.handleFormSubmissionResponse = function() {
        this.modal.hide();
        var strings = Str.get_strings([
            {
                key: "success",
                component: 'local_challenge'
            }
        ]);
        strings.then(function(string) {
            Y.use('moodle-core-formchangechecker', function() {
                M.core_formchangechecker.reset_form_dirty_state();
            });
            Notification.addNotification({
                message: string[0],
                type: "success"
            });
            setTimeout(function(){
                window.location.reload();
            }, 1000);
        });
    };
    challenge.prototype.handleFormSubmissionFailure = function(data) {
        this.modal.setBody(this.getBody(data));
    };
    var alter_status = function(args){
        var strings = Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'challenge_confirm_'+args.action,
                component: 'local_challenge',
                param :args   
            },
            {
                key: args.action,
                component: 'local_challenge'
            },
            {
                key: "success",
                component: 'local_challenge'
            }
            ]);
        strings.then(function(string) {
            // Create the modal.
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: string[0],
                body: string[1]
            }).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;
                
                this.modal.show();
                this.modal.setSaveButtonText(string[2]);

                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    modal.destroy();
                }.bind(this));
                this.modal.getRoot().on(ModalEvents.save, function(e) {
                    var methodname = 'local_challenge_alter_challenge_status';
                    var params = {};
                    params.contextid = args.contextid;
                    params.challengeid = args.challengeid;
                    params.newstatus = args.newstatus;

                    var promise = Ajax.call([{
                        methodname: methodname,
                        args: params,
                    }]);
                    promise[0].done(function(resp){
                        Notification.addNotification({
                            message: string[3],
                            type: "success"
                        });
                        setTimeout(function(){
                            window.location.reload();
                        }, 1000);
                    });
                    promise[0].fail(function(resp){
                        Notification.addNotification({
                            message: 'Cannot process',
                            type: "error"
                        });
                        setTimeout(function(){
                            window.location.reload();
                        }, 1000);
                    });        
                });
            

            }.bind(this));
        }.bind(this));

        
    };
    return {
    	init : function(){
    		$(document).on('click', '.challenge_trigger_element', function(){
    			var args = $(this).data();
    			return new challenge(args);
    		});
            $(document).on('click', '.challenge_status_trigger', function(){
                var args = $(this).data();
                return new alter_status(args);
            });
    	}
    };
});