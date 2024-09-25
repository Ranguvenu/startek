/**
 * Add a create new group modal to the page.
 *
 * @module     core_group/AjaxForms
 * @class      AjaxForms
 * @package    core_group
 * @copyright  2017 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',
        'core/str',
        'core/modal_factory',
        'core/modal_events',
        'core/fragment',
        'core/ajax',
        'core/yui',
        'core/templates',
        'local_certification/select2',
        'local_certification/certification'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y, Templates, select2, Certification) {

    /**
     * Constructor
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    certificationlastchildpopup=function(args) {
            $.ajax({
                type: "POST",
                url:   M.cfg.wwwroot + '/local/certification/ajax.php',
                data: { certificationid: args.id,action:'certificationlastchildpopup',
                    sesskey: M.cfg.sesskey
                },
                success: function(returndata) {
                    //Var returned_data is ONLY available inside this fn!
                        ModalFactory.create({
                        title: Str.get_string('certification_info', 'local_certification'),
                        body: returndata
                      }).done(function(modal) {
                        // Do what you want with your new modal.
                        modal.show();
                         modal.setLarge();
                         modal.getRoot().addClass('openLMStransition');
                            modal.getRoot().animate({"right":"0%"}, 500);
                            modal.getRoot().on(ModalEvents.hidden, function() {
                            modal.getRoot().animate({"right":"-85%"}, 500);
                                    setTimeout(function(){
                                    modal.destroy();
                                }, 1000);
                            }.bind(this));
                            $(".close").click(function(){
                                window.location.href =  window.location.href;
                            });
                         //return modal;
                      });
                }
            });
    };
    var AjaxForms = function(args) {
        this.contextid = args.contextid;
        this.args = args;
        this.init(args);
    };

    /**
     * @var {Modal} modal
     * @private
     */
    AjaxForms.prototype.modal = null;

    /**
     * @var {int} contextid
     * @private
     */
    AjaxForms.prototype.contextid = -1;

    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    AjaxForms.prototype.init = function(args) {
        // Fetch the title string.
        var self = this;
        //console.log(args);
            switch (args.callback) {
                case 'certification_form':
                        switch (args.id) {
                            case 0:
                                header_label='createcertification';
                                break;
                            default:
                                 header_label='updatecertification';
                        }
                    break;
                case 'session_form':
                        switch (args.id) {
                            case 0:
                                header_label='addsession';
                                break;
                            default:
                                 header_label='updatesession';
                        }
                    break;
                case 'course_form':
                        switch (args.id) {
                            case 0:
                                header_label='addcourses';
                                break;
                            default:
                                 header_label='updatecourses';
                        }
                    break;
                case 'certification_completion_form':
                                 header_label='certification_completion_settings';
                break;
            }
            var strings = Str.get_strings([{
                key: header_label,
                component: 'local_certification',
            },{
                key: 'savechanges',
                component: 'moodle'
            },{
                key: 'previous',
                component: 'moodle'
            },{
                key: 'skip',
                component: 'local_users'
            },{
                key: 'cancel',
                component: 'moodle'
            }]);
        return strings.then(function(str) {
            // Create the modal.
            return ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: str[0],
                body: this.getBody(),
                footer: this.getFooter(str),
            });
        }.bind(this)).then(function(modal) {
            // Keep a reference to the modal.
            this.modal = modal;

            // Forms are big, we want a big modal.
            this.modal.setLarge();

            this.modal.getRoot().addClass('openLMStransition local_certification');

            // We want to reset the form every time it is opened.
            this.modal.getRoot().on(ModalEvents.hidden, function() {
                this.modal.getRoot().animate({"right":"-85%"}, 500);
                setTimeout(function(){
                    modal.destroy();
                }, 1000);
                // this.modal.destroy();
            }.bind(this));
            this.modal.getFooter().find('[data-action="save"]').on('click', this.submitForm.bind(this));
            this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                window.location.href =  window.location.href;
                //modal.hide();
                //setTimeout(function(){
                //    modal.destroy();
                //}, 1000);
                // modal.destroy();
            });
            this.modal.getFooter().find('[data-action="skip"]').on('click', function() {
                self.args.form_status = self.args.form_status + 1;
                 //console.log(args.form_status);
                if (args.form_status == 4) {
                // OL-1042 Add Target Audience to Certifications//
                    certificationlastchildpopup(args);
                 }
                var data = self.getBody();
                data.then(function(html, js) {
                    if (html === false) {
                        // window.location.reload();
                        self.handleFormSubmissionResponse(args);
                        $('#viewcertifications').dataTable().destroy();
                        Certification.Datatable();
                    }
                });
                modal.setBody(data);
                if(self.args.form_status == 3){
                    $('[data-action="skip"]').css('display', 'none');
                }
            });
            this.modal.getFooter().find('[data-action="previous"]').on('click', function() {
                self.args.form_status = self.args.form_status - 1;
                var data = self.getBody();
                data.then(function(html, js) {
                    if (html === false) {
                        // window.location.reload();
                        self.handleFormSubmissionResponse(args);
                        
                    }
                });
                modal.setBody(data);
                if (self.args.form_status == 0) {
                    $('[data-action="skip"]').css('display', 'none');
                    $('[data-action="previous"]').css('display', 'none');
                }else if(self.args.form_status < 3){
                    $('[data-action="skip"]').css('display', 'inline-block');
                    $('[data-action="previous"]').css('display', 'inline-block');
                }
            });
            if(self.args.callback == 'certification_form'){
                // if(instanceid > 0){
                $(document).on('click', '.custom_certification_form_redirect', function(){
                    var instanceid = $("#certificationid").val();
                    if(instanceid > 0){
                        self.args.form_status = $(this).data('value');
                        var data = self.getBody();
                        data.then(function(html, js) {
                            if (html === false) {
                              // window.location.reload();
                                self.handleFormSubmissionResponse(args);
                            }
                        });
                        modal.setBody(data);
                        if (self.args.form_status == 0) {
                            $('[data-action="skip"]').css('display', 'none');
                            $('[data-action="previous"]').css('display', 'none');
                        }else if(self.args.form_status < 4){
                            $('[data-action="skip"]').css('display', 'inline-block');
                            $('[data-action="previous"]').css('display', 'inline-block');
                        }
                    }
                });
            }

            this.modal.getRoot().on('submit', 'form', function(form) {
                self.submitFormAjax(form, self.args);
            });
            this.modal.show();
            this.modal.getRoot().animate({"right":"0%"}, 500);
            $(".close").click(function(){
              window.location.href =  window.location.href;
            });
            return this.modal;
        }.bind(this));
    };

    /**
     * @method getBody
     * @private
     * @return {Promise}
     */
    AjaxForms.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
//console.log('#########');
//        console.log(formdata);
        // Get the content of the modal.
        this.args.jsonformdata = JSON.stringify(formdata);
        return Fragment.loadFragment(this.args.component, this.args.callback, this.contextid, this.args);
    };
    /**
     * @method getFooter
     * @private
     * @return {Promise}
     */
    AjaxForms.prototype.getFooter = function(str) {
        // var templateContext;
        // var modalPromise = Templates.render('local_certification/form_actions', templateContext)
        // .done(function(html) {
        // console.log(html);
        // });

        $footer = '<button type="button" class="btn btn-primary" data-action="save">'+str[1]+'</button>&nbsp;';
        if (this.args.form_status == 0) {
            $style = 'style="display:none;"';
            $footer += '<button type="button" class="btn btn-secondary" data-action="previous" ' + $style + '>'+str[2]+'</button>&nbsp;';
            $footer += '<button type="button" class="btn btn-secondary" data-action="skip" ' + $style + '>'+str[3]+'</button>&nbsp;';
        }
        $footer += '<button type="button" class="btn btn-secondary" data-action="cancel">'+str[4]+'</button>';
        return $footer;
    };
    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    AjaxForms.prototype.handleFormSubmissionResponse = function(args) {
        this.modal.hide();
        // We could trigger an event instead.
        // Yuk.
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
        if (args.form_status == -2) {
            window.location.reload();
        }
        // This will be the context for our template. So {{name}} in the template will resolve to "Tweety bird".
        // var context = { id: args.id};

        // var modalPromise = ModalFactory.create({
        //     type: ModalFactory.types.DEFAULT,
        //     body: Templates.render('local_certification/certificationview', context),
        // });
        // $.when(modalPromise).then(function(modal) {
        //     modal.setTitle('Hi');
        //     modal.show();
        //     return modal;
        // }).fail(Notification.exception);
        //  switch (args.callback) {
        //         case 'certification_form':
        //                 switch (args.id) {
        //                     case 0:
        //                         header_label='createcertification';
        //                         break;
        //                     default:
        //                          header_label='updatecertification';
        //                 }
        //             break;
        // }
        //console.log(args.form_status);
        certificationlastchildpopup(args);
        //$('#viewcertifications').dataTable().destroy();
        //Certification.Datatable();
    };

    /**
     * @method handleFormSubmissionFailure
     * @private
     * @return {Promise}
     */
    AjaxForms.prototype.handleFormSubmissionFailure = function(data) {
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
    AjaxForms.prototype.submitFormAjax = function(e, args) {
        // We don't want to do a real form submission.
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        var methodname = args.plugintype + '_' + args.pluginname + '_submit_instance';
        // Now we can continue...
        var params = {};
        params.contextid = this.contextid;
        params.jsonformdata = JSON.stringify(formData);
        params.form_status = args.form_status;

        var promise = Ajax.call([{
            methodname: methodname,
            args: params
        }]);
        promise[0].done(function(resp){
            self.args.form_status = resp.form_status;
            if (resp.form_status >= 0 && resp.form_status !== false) {
                self.args.form_status = resp.form_status;
                self.args.id = resp.id;
                self.handleFormSubmissionFailure();
            } else {
                self.handleFormSubmissionResponse(self.args);
            }
            if (args.form_status > 0) {
                $('[data-action="skip"]').css('display', 'inline-block');
                $('[data-action="previous"]').css('display', 'inline-block');
            }
            if (args.form_status == 3 ) {
                $('[data-action="skip"]').css('display', 'none');
            }
        }).fail(function(ex){
            self.handleFormSubmissionFailure(formData);
        });

    };

    /**
     * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
     *
     * @method submitForm
     * @param {Event} e Form submission event.
     * @private
     */
    AjaxForms.prototype.submitForm = function(e) {
        e.preventDefault();
        this.modal.getRoot().find('form').submit();
    };

    return /** @alias module:core_group/AjaxForms */ {
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
            return new AjaxForms(args);
        },
        load: function () {

        }
    };
});
