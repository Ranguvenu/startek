/**
 * Add a create new team modal to the page.
 *
 * @module     local_users/EditUserPw
 * @class      EditUserPw
 * @package    local_users
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {
 
    /**
     * Constructor
     *
     * @param {object} args
     *
     * Each call to init gets it's own instance of this class.
     */
    var EditUserPw = function(args) {
        this.contextid = args.context;
        this.id = args.id;
        this.args = args;
        var self = this;
        self.init();
    };
 
    /**
     * @var {Modal} modal
     * @private
     */
    EditUserPw.prototype.modal = null;
 
    /**
     * @var {int} contextid
     * @private
     */
    EditUserPw.prototype.contextid = -1;
 
    /**
     * Initialise the class.
     *
     * @private
     * @return {Promise}
     */
    EditUserPw.prototype.init = function() {
        var self = this;
        var editid = $(this).data('value');
         if (self.id) {
             self.id = editid;
             var head = Str.get_string('edituser', 'local_users');
        }
        else{
              var head = Str.get_string('adnewuser', 'local_users');
           }

        return head.then(function(title) {
            // Create the modal.
            return ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: title,
                body: self.getBody()
            });
        }.bind(self)).then(function(modal) {
            
            // Keep a reference to the modal.
            self.modal = modal;
           
            // self.modal.getRoot().addClass('openLMStransition local_users');
            // Forms are big, we want a big modal.
            // self.modal.setLarge();
 
            // We want to reset the form every time it is opened.
            self.modal.getRoot().on(ModalEvents.hidden, function() {
                self.modal.setBody(self.getBody());
                self.modal.getRoot().animate({"right":"-85%"}, 500);
                setTimeout(function(){
                    modal.destroy();
                }, 1000);
                
            }.bind(this));

            
            // We want to hide the submit buttons every time it is opened.
            self.modal.getRoot().on(ModalEvents.shown, function() {
                self.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
            }.bind(this));
 

            // We catch the modal save event, and use it to submit the form inside the modal.
            // Triggering a form submission will give JS validation scripts a chance to check for errors.
            self.modal.getRoot().on(ModalEvents.save, self.submitForm.bind(self));
            // We also catch the form submit event and use it to submit the form with ajax.
            self.modal.getRoot().on('submit', 'form', self.submitFormAjax.bind(self));

            this.modal.show();
            this.modal.getRoot().animate({"right":"0%"}, 500);
            return this.modal;
        }.bind(this));
        
    };
 
    /**
     * @method getBody
     * @private
     * @return {Promise}
     */
    EditUserPw.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        var params = {id:this.args.id, jsonformdata: JSON.stringify(formdata)};
        return Fragment.loadFragment('local_users', 'edit_user_pw', this.args.context, params);
        // return 'here';
    };
 
    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    EditUserPw.prototype.handleFormSubmissionResponse = function() {
        this.modal.hide();
        // We could trigger an event instead.
        // Yuk.
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
        document.location.reload();
    };
 
    /**
     * @method handleFormSubmissionFailure
     * @private
     * @return {Promise}
     */
    EditUserPw.prototype.handleFormSubmissionFailure = function(data) {
        // Oh noes! Epic fail :(
        // Ah wait - this is normal. We need to re-display the form with errors!
        this.modal.setBody(this.getBody(data));
    };
 
    /**
     * Private method
     *
     * @method submitFormAjax
     * @private
     * @param {Event} e Form submission event.
     */
    EditUserPw.prototype.submitFormAjax = function(e) {
        // We don't want to do a real form submission.
        e.preventDefault();
 
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        // Now we can continue...
        Ajax.call([{
            methodname: 'local_users_submit_edit_user_pw_form',
            args: {contextid: this.contextid, jsonformdata: JSON.stringify(formData)},
            done: this.handleFormSubmissionResponse.bind(this, formData),
            fail: this.handleFormSubmissionFailure.bind(this, formData)
        }]);
    };
 
    /**
     * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
     *
     * @method submitForm
     * @param {Event} e Form submission event.
     * @private
     */
    EditUserPw.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };
    
    return /** @alias module:local_users/EditUserPw */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {object} args
         * @return {Promise}
        */
        init: function(args) {
            return new EditUserPw(args);
        },
        load: function(){

        }
    };
});