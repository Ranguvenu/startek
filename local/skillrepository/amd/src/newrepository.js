/**
 * Add a create new group modal to the page.
 *
 * @module     local_costcenter/costcenter
 * @class      NewCostcenter
 * @package    local_costcenter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {
 
    /**
     * Constructor
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var NewRepository = function(args) {
        this.contextid = args.contextid;
        this.repositoryid = args.repositoryid;
        var self = this;
        self.init(args);
    };
 
    /**
     * @var {Modal} modal
     * @private
     */
    NewRepository.prototype.modal = null;
 
    /**
     * @var {int} contextid
     * @private
     */
    NewRepository.prototype.contextid = -1;
 
    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    NewRepository.prototype.init = function(args) {
        console.log(args);
        //var triggers = $(selector);
        var self = this;
        // Fetch the title string.
        // $('.'+args.selector).click(function(){ 
            // var editid = $(this).data('value');
            if (args.repositoryid) {
                // self.repositoryid = editid;
                var head = Str.get_string('editrepository', 'local_skillrepository');
            }
            else {
               var head = Str.get_string('adnewrepository', 'local_skillrepository');
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
                // self.modal.show();
                // Forms are big, we want a big modal.
                self.modal.setLarge();
                this.modal.getRoot().addClass('openLMStransition local_skillrepository');
     
                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.getRoot().animate({"right":"-85%"}, 500);
                    setTimeout(function(){
                        modal.destroy();
                    }, 100);
                }.bind(this));
                // self.modal.getRoot().on(ModalEvents.hidden, function() {
                //     self.modal.setBody(self.getBody());
                // }.bind(this));
                    self.modal.getRoot().on(ModalEvents.shown, function() {
                    self.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                    this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.hide();
                        setTimeout(function(){
                            modal.destroy();
                        }, 5000);
                        // modal.destroy();
                    });
                }.bind(this));
                // We want to hide the submit buttons every time it is opened.
                
                // self.modal.getRoot().on(ModalEvents.shown, function() {
                //     self.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                // }.bind(this));
    
                // We catch the modal save event, and use it to submit the form inside the modal.
                // Triggering a form submission will give JS validation scripts a chance to check for errors.
                self.modal.getRoot().on(ModalEvents.save, self.submitForm.bind(self));
                // We also catch the form submit event and use it to submit the form with ajax.
                self.modal.getRoot().on('submit', 'form', self.submitFormAjax.bind(self));
                self.modal.show();
                this.modal.getRoot().animate({"right":"0%"}, 500);
                return this.modal;
            }.bind(this));     
        // });        
    };
 
    /**
     * @method getBody
     * @private
     * @return {Promise}
     */
    NewRepository.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        var params = {repositoryid:this.repositoryid, jsonformdata: JSON.stringify(formdata)};
        

        return Fragment.loadFragment('local_skillrepository', 'new_skill_repository_form', this.contextid, params);
    
    };
 
    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    NewRepository.prototype.handleFormSubmissionResponse = function() {
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
    NewRepository.prototype.handleFormSubmissionFailure = function(data) {
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
    NewRepository.prototype.submitFormAjax = function(e) {
        // We don't want to do a real form submission.
        e.preventDefault();
 
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        
        // Now we can continue...
        Ajax.call([{
            methodname: 'local_skillrepository_submit_skill_repository_form_form',
            args: {contextid: this.contextid, jsonformdata: formData},
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
    NewRepository.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };
 
    return /** @alias module:local_costcenter/newcostcenter */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {string} selector The CSS selector used to find nodes that will trigger this module.
         * @param {int} contextid The contextid for the course.
         * @return {Promise}
         */
        init: function(args) {
          
            
            return new NewRepository(args);
        },
        load: function(){

        },
        deleteskill: function(args) {
            console.log(args);
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'deleteskill',
                component: 'local_skillrepository',
                param :args
            },
            {
                key: 'delete',
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: s[1]
                })/*.done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[2]);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        $.ajax({
                            method: "POST",
                            dataType: "json",
                            url: M.cfg.wwwroot + "/local/skillrepository/ajax.php?action="+args.selector+"&skillid="+args.skillid,
                            success: function(data){
                                window.location.reload();
                            }
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));*/
                .done(function(modal) {
                    this.modal = modal;
                    //modal.setSaveButtonText("Yes");

                   modal.setSaveButtonText(Str.get_string('yes_delete', 'local_skillrepository'));


                //For cancel button string changed//
                var value=Str.get_string('cancel', 'local_skillrepository');
                var button = this.modal.getFooter().find('[data-action="cancel"]');
                this.modal.asyncSet(value, button.text.bind(button));

                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        console.log(args);
                        var params = {};
                        params.id = args.skillid;
                        params.contextid = args.contextid;
                    
                        var promise = Ajax.call([{
                            methodname: 'local_skillrepository_delete_skill',
                            args: params
                        }]);
                        promise[0].done(function(resp) {
                            window.location.href = window.location.href;
                        }).fail(function(ex) {
                            // do something with the exception
                             console.log(ex);
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        nodeleteskill: function (args) {
        return Str.get_strings([{
        key: 'reason',
        component: 'local_skillrepository'
        },
        {
        key: 'deleteskillnotconfirm',
        component: 'local_skillrepository',
        param: args
        }]).then(function (s) {
        ModalFactory.create({
         title: s[0],
         type: ModalFactory.types.DEFAULT,
         body: s[1],
        }).done(function (modal) {
         this.modal = modal;
         modal.show();
        }.bind(this));
        }.bind(this));
        },
    };
});