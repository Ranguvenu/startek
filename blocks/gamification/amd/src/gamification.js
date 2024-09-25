/**
 * Add a create new group modal to the page.
 *
 * @module     block_gamification/gamification
 * @class      gamification
 * @package    block_gamification
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['local_courses/jquery.dataTables', 'jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function (dataTable, $, Str, ModalFactory, ModalEvents, Fragment, Ajax) {
    var levelsform = function(args){
        this.contextid = args.contextid;
        // this.id = args.id;
        var self = this;
        this.args = args;
        self.init(args);
    };
    /**
     * @var {Modal} modal
     * @private
     */
    levelsform.prototype.modal = null;
 
    /**
     * @var {int} contextid
     * @private
     */
    levelsform.prototype.contextid = -1;
 
    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    levelsform.prototype.init = function(args) {
        //var triggers = $(selector);
        var self = this;



        // Fetch the title string.
        // $('.'+args.selector).click(function(){
            

            // var editid = $(this).data('value');
            if (this.args.configid > 0) {
                self.configid = this.args.configid;
            }
            if(self.configid){
                var head =  Str.get_string('editlevels', 'block_gamification');
            }
            else{
               var head = Str.get_string('addnewlevels', 'block_gamification');
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
               
                self.modal.getRoot().addClass('openLMStransition local_costcenter');
                // Forms are big, we want a big modal.
                self.modal.setLarge();
     
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
        
        
        // });
        
    };
    /**
     * @method getBody
     * @private
     * @return {Promise}
     */
    levelsform.prototype.getBody = function(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        // console.log(this.args);
        // alert(this.args);
        // alert(formdata);
        // Get the content of the modal.
        var params = {costcenterid:this.args.costcenterid,levels: this.args.levels, enabled:this.args.enabled, jsonformdata: JSON.stringify(formdata)};
        return Fragment.loadFragment('block_gamification', 'levelsform', this.contextid, params);
    };
    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    levelsform.prototype.handleFormSubmissionResponse = function() {
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
    levelsform.prototype.handleFormSubmissionFailure = function(data) {
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
    levelsform.prototype.submitFormAjax = function(e) {
        // We don't want to do a real form submission.
        e.preventDefault();
 
        // Convert all the form elements values to a serialised string.
        var formData = this.modal.getRoot().find('form').serialize();
        // alert(this.contextid);
        // Now we can continue...
        Ajax.call([{
            methodname: 'block_gamification_submit_levels_form',
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
    levelsform.prototype.submitForm = function(e) {
        e.preventDefault();
        var self = this;
        self.modal.getRoot().find('form').submit();
    };
    var displaylevelscontent = function(args) {
        //var triggers = $(selector);
        var self = this;
        // Fetch the title string.
        // $(selector).click(function(){
            
            // var editid = $(this).data("value");
            // //alert(editid);
            // if(typeof this.editid != 'undefined'){
            //         editid=0;
            // }
            //  self.categoryid = editid;
              //alert(self.courseid);
            return Str.get_string('leveldconfigdata', 'block_gamification',self).then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.CANCEL,
                    title: title,
                    body: self.getlevelbody(args)
                });
            }.bind(self)).then(function(modal) {
                
                // Keep a reference to the modal.
                self.modal = modal;
                self.modal.show();
                // Forms are big, we want a big modal.
                self.modal.setLarge();
     
                // We want to reset the form every time it is opened.
                self.modal.getRoot().on(ModalEvents.hidden, function() {
                    self.modal.setBody('');
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
                return this.modal;
            }.bind(this));       
        
        
        // });
        
    };
    displaylevelscontent.prototype.getlevelbody = function(args){
        return Fragment.loadFragment('block_gamification', 'levelscontent', 1, args);
    };
    var displayLevelUserPopup = function(args){
        //var triggers = $(selector);
        var self = this;
        
            return Str.get_string('levelusersdata', 'block_gamification',args).then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: title,
                    body: self.getlevelusersbody(args)
                });
            }.bind(self)).then(function(modal) {
                
                // Keep a reference to the modal.
                self.modal = modal;
                self.modal.show();
                // Forms are big, we want a big modal.
                self.modal.setLarge();
     
                // We want to reset the form every time it is opened.
                self.modal.getRoot().on(ModalEvents.hidden, function() {
                    self.modal.setBody('');
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
                return this.modal;
            }.bind(this));       
        
        
        // });
        
    };
    displayLevelUserPopup.prototype.getlevelusersbody = function(args){
        return Fragment.loadFragment('block_gamification', 'levelsuserscontent', 1, args);
    };
    var validateGradeElement = function(element){
        var htmlobj = $(element);
        var flag1 = flag2 = flag3 = true;
        var lowervalue = $(element).find('.lowervalue_element').val();
        if(lowervalue == ''){
            $(element).find('.error_lowervalue_element').show();
            flag1 = false;
        }else{
            $(element).find('.error_lowervalue_element').hide();
        }
        var uppervalue = $(element).find('.uppervalue_element').val();
        if(uppervalue == ''){
            $(element).find('.error_uppervalue_element').show();
            flag2 = false;
        }else{
            $(element).find('.error_uppervalue_element').hide();
        }
        if($(element).find('.select.custom-select').val() == null){
            $(element).find('.error_completion_setting').show();
            flag3 = false;
        }else{
            $(element).find('.error_completion_setting').hide();
        }
        console.log(lowervalue == '');
        console.log(typeof(uppervalue));
        console.log(lowervalue);
        console.log(uppervalue);
        console.log(flag1 && flag2 && (parseInt(lowervalue) > parseInt(uppervalue)));
        if(flag1 && flag2 && (parseInt(lowervalue) > parseInt(uppervalue))){
            $(element).find('.error_range_element_mismatch').show();
            flag1 = false;
            flag2 = false;
        }else{
            $(element).find('.error_range_element_mismatch').hide();
        }

        return (flag1 && flag2 && flag3);
    };
    var validateCompletionElement = function(element){
        if($(element).find('.select.custom-select').val() != null){
            $(element).find('.error_completion_setting').hide();
            return true;
        }else{
            $(element).find('.error_completion_setting').show();
            return false;
        }
    };
    return {
        load :function(){

        },
        levelsform: function(){
            $(document).on('click', '.submitlevels', function(){
                var levelsid = $(this).attr('id');
                var levels = $('.'+levelsid).val();
                var maxlevels = $(this).data('maxlevels');
                if(levels > maxlevels){
                    $('.errorlevel'+levelsid).removeClass('hidden');
					 Str.get_string('maximum_number_of_levels', 'block_gamificationy').then(function(maxleveltext) {
						$('.errorlevel'+levelsid).html(maxleveltext+' '+maxlevels);
					 });
                    
                }else if(levels >= 2){
                    var costcenterid = $('.'+levelsid).data('costcenterid');
                    var enabled = 1//$('.enabledgamification'+costcenterid).prop("checked");
                    var configid = $('.'+levelsid).data('configid');
                    args = {costcenterid: costcenterid, contextid: 1, enabled: enabled, levels: levels, configid: configid};
                    return new levelsform(args);
                }else{
                    $('.errorlevel'+levelsid).removeClass('hidden');
                    if(levels && levels<2)
						Str.get_string('minimum_number_of_levels', 'block_gamificationy').then(function(minleveltext) {
							$('.errorlevel'+levelsid).html(minleveltext);
						});
                    else
						Str.get_string('levelsmandatory', 'block_gamificationy').then(function(levelsmandatory) {
							$('.errorlevel'+levelsid).html(levelsmandatory);
						});
                }
            });
            $(document).on('change', '.levelvalue' , function(){
                var costcenterid = $(this).data('costcenterid');
                var exist = $('.errorlevelsubmit'+costcenterid).hasClass('hidden');
                if(!exist)
                    $('.errorlevelsubmit'+costcenterid).addClass('hidden');
            });
            $(document).on('click', '.displaylevels', function(){
                var costcenterid = $(this).data('costcenterid');
                args = {costcenterid: costcenterid};
                return new displaylevelscontent(args);
            });
            $(document).on('click', '.leveluserdisplaypopup', function(){
                var value = $(this).data('value');
                if(value){
                    var costcenterid = $(this).data('costcenterid');
                    var level = $(this).data('level');
                    args = {costcenterid: costcenterid, level: level};
                    return new displayLevelUserPopup(args);
                }
            });
            // $(document).on('click', '.slider.round', function(){
            //     var id = $(this).data('id');
            //     var value = $('.enabledgamification'+id).prop("checked");
            //     alert(id);
            //     alert(value);
            //     if(value == 1){
            //         $(this).html('No');
            //     }else if(value == 0){
            //         $(this).html('Yes');
            //     }
            // });
        },
        badgespreview: function(){
            $(document).on('change', '#badgeaccountselect' , function(){
                var costcenter = $(this).find("option:selected").val();
                $.ajax({
                    method: "GET",
                    dataType: "json",
                    url: M.cfg.wwwroot + "/blocks/gamification/customajax.php?action=get_costcenter_badges&costcenter="+costcenter,
                    success: function(data){
                        $('#badges_container').html(data);
                    }
                });
            });
        },
        config_datatables: function(){
            $('#config_table').dataTable( {
                searching: true,
                pageLength: 5,
                responsive: true,
                bLengthChange: false,
                aaSorting: [],
                oLanguage: {
                    oPaginate: {
                        sNext:   ' > ',
                        bStateSave: true,
                        sPrevious: ' < '
                    }
                }
            });
        },
        ruleDefinationValidate: function(){
            // $(document).ready(function(){
            // $('.block-gamification-filters').find('[type="submit"]').click(function(e){
                // // $('.block-gamification-filters').find('[type="submit"]').removeClass('hidden');
                // // $('.block-gamification-filters').find('[type="submit"]').removeClass('hidden');
                // $(document).on('click', '#filter_from_submit', function(e){
                //     var returnval = true;
                //     e.stopPropagation();
                //     var rules = $('.custom_gamification_rule');
                //     rules.each(function(index, value){
                //         // alert(value.attr('class'));
                //         if($(value).hasClass('ruletype_grade')){
                //             // console.log('flag');
                //             flag = validateGradeElement(value);
                //             // console.log(flag);
                //         }else if ($(value).hasClass('ruletype_completetion')){
                //             // console.log('flag');
                //             flag = validateCompletionElement(value);
                //             // console.log(flag);
                //         }
                //         console.log('flag');
                //         console.log(flag);
                //         if(!flag){
                //             returnval = false;
                //             console.log(returnval);
                //         }
                //     });
                //     // if(returnval){
                //     //     console.log('herecheck');
                //         return returnval;
                //     // }else{
                //         // return false;
                //     // }
                // });
            // });
            // $('.block-gamification-filters').find('[type="submit"]').removeClass('hidden');
            $('.block-gamification-filters').on('submit', function(e) {
                // alert('hi');
                if (!skipClientValidation) {
                    var returnval = true;
                    var rules = $('.custom_gamification_rule');
                    rules.each(function(index, value){
                        // alert(value.attr('class'));
                        if($(value).hasClass('ruletype_grade')){
                            // console.log('flag');
                            flag = validateGradeElement(value);
                            // console.log(flag);
                        }else if ($(value).hasClass('ruletype_completetion')){
                            // console.log('flag');
                            flag = validateCompletionElement(value);
                            console.log(flag);
                        }
                        console.log('flag');
                        console.log(flag);
                        if(!flag){
                            returnval = false;
                            console.log(returnval);
                        }
                    });
                    // if(returnval){
                    //     console.log('herecheck');
                        return returnval;
                }else{
                    window.location.reload();
                    // return true;
                }
            });
        }
    };
});