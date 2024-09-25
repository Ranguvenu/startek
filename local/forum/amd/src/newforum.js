/**
 * Add a create new group modal to the page.
 *
 * @module     local_forum/newforum
 * @class      newforum
 * @package    local_forum
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['local_courses/jquery.dataTables', 'jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function(DataTable, $, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {
 
    /**
     * Constructor
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var NewForum = function(args) {

        this.contextid = args.context;
        this.id = args.id;
        var self = this;
        this.args = args;
        self.init(args);
    };
 
    /**
     * @var {Modal} modal
     * @private
     */
    NewForum.prototype.modal = null;
 
    /**
     * @var {int} contextid
     * @private
     */
    NewForum.prototype.contextid = -1;
 
    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    NewForum.prototype.init = function(args) {
        //var triggers = $(selector);
        var self = this;
        // Fetch the title string.
            if (self.id) {
                head =  Str.get_string('editforum', 'local_forum');
            }else{
               head =  Str.get_string('forum:addinstance', 'local_forum');
            }
            return head.then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: title,
                body: this.getBody(),
                footer: this.getFooter(),
                });
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;
                // self.modal.show();
                // Forms are big, we want a big modal.
                this.modal.setLarge(); 
                
                this.modal.getRoot().addClass('openLMStransition');

                // this.modal.getRoot().on(ModalEvents.hidden, function() {
                //     this.modal.setBody('');
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.getRoot().animate({"right":"-85%"}, 500);
                    setTimeout(function(){
                        modal.destroy();
                    }, 1000);
                }.bind(this));

                this.modal.getFooter().find('[data-action="save"]').on('click', this.submitForm.bind(this));
                // We also catch the form submit event and use it to submit the form with ajax.

                // this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                //     modal.setBody('');
                //     modal.hide();
                this.modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                    modal.hide();
                    setTimeout(function(){
                        modal.destroy();
                    }, 1000);
                     //modal.destroy();
                    if (args.form_status !== 0 ) {
                        window.location.reload();
                    }
                });
                this.modal.getRoot().find('[data-action="hide"]').on('click', function() {
                    modal.hide();
                    setTimeout(function(){
                        modal.destroy();
                    }, 1000);
                     //modal.destroy();
                    if (args.form_status !== 0 ) {
                        window.location.reload();
                    }
                });

                this.modal.getRoot().on('submit', 'form', function(form) {
                    self.submitFormAjax(form, self.args);
                });
                this.modal.show();
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
    NewForum.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // Get the content of the modal.
        this.args.jsonformdata = JSON.stringify(formdata);
        return Fragment.loadFragment('local_forum', 'new_forum_form', this.contextid, this.args);
    };
    /**
     * @method getFooter
     * @private
     * @return {Promise}
     */
    NewForum.prototype.getFooter = function() {
        $footer = '<button type="button" class="btn btn-primary" data-action="save">'+M.util.get_string("save_continue", "local_forum")+'</button>&nbsp;';
        $style = 'style="display:none;"';
        //$footer += '<button type="button" class="btn btn-secondary" data-action="skip" ' + $style + ' >Skip</button>&nbsp;';
        $footer += '<button type="button" class="btn btn-secondary" data-action="cancel">'+M.util.get_string("cancel", "moodle")+'</button>';
        return $footer;
    };
 
    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    NewForum.prototype.handleFormSubmissionResponse = function(args) {
        this.modal.hide();
        // We could trigger an event instead.
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
        document.location.reload();
        //var modalPromise = ModalFactory.create({
        //    type: ModalFactory.types.DEFAULT,
        //    body: this.getBody(),
        //});
        //$.when(modalPromise).then(function(modal) {
        //
        //    modal.show();
        //    return modal;
        //}).fail(Notification.exception);
    };
 
    /**
     * @method handleFormSubmissionFailure
     * @private
     * @return {Promise}
     */
    NewForum.prototype.handleFormSubmissionFailure = function(data) {
         // We need to re-display the form with errors!
        this.modal.setBody(this.getBody(data));
    };
 
    /**
     * Private method
     *
     * @method submitFormAjax
     * @private
     * @param {Event} e Form submission event.
     */
    NewForum.prototype.submitFormAjax = function(e ,args) {
        // We don't want to do a real form submission.
        e.preventDefault();
        var self = this;
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        var methodname = 'local_forum_submit_create_forum_form';
        var params = {};
        params.contextid = this.contextid;
        params.form_status = args.form_status;
        params.jsonformdata = JSON.stringify(formData);
        

        var promise = Ajax.call([{
            methodname: methodname,
            args: params
        }]);
         promise[0].done(function(resp){
            // alert(resp.form_status);
            if(resp.form_status !== -1 && resp.form_status !== false) {
                self.args.form_status = resp.form_status;
                self.args.id = resp.id;
                self.handleFormSubmissionFailure();
                if (resp.form_status == 2) {
                    //code
                     self.handleFormSubmissionResponse(self.args);
                }
            } else {
                self.handleFormSubmissionResponse(self.args);
            }
            if(args.form_status > 0) {
                $('[data-action="skip"]').css('display', 'inline-block');
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
    NewForum.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };
 
    return {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         * @method init
         * @param {args} 
         */
        init: function(args) {
            $(document).on('change', '#id_costcenterid', function() {
                
                var costcentervalue = $(this).find("option:selected").val();
                title = M.util.get_string("all", "moodle");
                if (costcentervalue !== null) {
                    $.ajax({
                        method: "GET",
                        dataType: "json",
                        url: M.cfg.wwwroot + "/local/forum/dept_ajax.php?action=departmentlist&costcenter="+costcentervalue,
                        success: function(data){
                            var depttemplate = '<option value="">'+title+'</option>';
                              $.each( data.data, function( index, value) {
                                   depttemplate +=	'<option value = ' + index + ' >' +value + '</option>';
                                });
                            $("#id_departmentid").html(depttemplate);
                        }
                    });
                    grptitle = M.util.get_string("all", "moodle");
                    $.ajax({
                        method: "GET",
                        dataType: "json",
                        url: M.cfg.wwwroot + "/local/forum/dept_ajax.php?action=groupslist&costcenter="+costcentervalue,
                        success: function(data){
                            var template = '<option value="">'+grptitle+'</option>';
                              $.each( data.data, function( index, value) {
                                   template +=	'<option value = ' + index + ' >' +value+ '</option>';
                              });
                            $("#id_local_group").html(template);
                        }
                    });
                }
            });
            
            $(document).on('change', '#id_departmentid', function() {
                //var departmentvalue = $(this).find("option:selected").val();
                var departmentvalue = $('#id_departmentid').val();
                title = M.util.get_string("all", "moodle");
                //if (departmentvalue !== null && !isNaN(parseInt(departmentvalue, 10))) {
                if (departmentvalue !== null) {
                    if(departmentvalue > 0){
                        var costcentervalue = 0;
                    }else{
                        var costcentervalue = $('#id_costcenterid').val();
                    }
                    $.ajax({
                        method: "GET",
                        dataType: "json",
                        url: M.cfg.wwwroot + "/local/forum/dept_ajax.php?action=groupslist&costcenter="+costcentervalue+"&department="+departmentvalue,
                        success: function(data){
                            var template = '<option value="">'+title+'</option>';
                                $.each( data.data, function( index, value) {
                                     template +=	'<option value = ' + index + ' >' +value+ '</option>';
                                });
                            $("#id_local_group").html(template);
                        }
                    });
                }
            });
            return new NewForum(args);
        },
        load: function(){},
        deleteforum: function(elem) {
            return Str.get_strings([{
                key: 'delete',
                component: 'local_forum'
            }, {
                key: 'deleteforum',
                component: 'local_forum'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">'+M.util.get_string("yes", "moodle")+'</button>&nbsp;' +
        '<button type="button" class="btn btn-secondary" data-action="cancel">'+M.util.get_string("no", "moodle")+'</button>'
                }).done(function(modal) {
                    this.modal = modal;
                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        window.location.href ='index.php?id='+elem+'&delete=1&sesskey=' + M.cfg.sesskey;
                    }.bind(this));
                    modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.setBody('');
                        modal.hide();
                    });
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        load_datatable :function(tableid){
            return Str.get_strings([{
                key: 'noforums',
                component: 'local_forum'
				},
				{
				key: 'search',
                component: 'local_forum'
				}
			
			]).then(function(s) {
                $('#'+tableid).DataTable({
                    'language': {
                        "zeroRecords": s[0],
                        'infoEmpty': s[0],
                        'paginate': {
                            'previous': '<',
                            'next': '>'
                        }
                    },
					"oLanguage": {
					"sSearch": s[1]
				 },
                    order: []
                });
            });
        },
        
    };
});