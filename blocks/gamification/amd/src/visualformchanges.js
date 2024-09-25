/**
 * Add a create new group modal to the page.
 *
 * @module     block_gamification/visualformchanges
 * @class      Visual form changes
 * @package    block_gamification
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function ($, Str, ModalFactory, ModalEvents, Fragment, Ajax) {
    'use strict';
    var visualformchanges = function (costcentervalue){
        $.ajax({
            method: "POST",
            dataType: "json",
            url: M.cfg.wwwroot + "/blocks/gamification/customajax.php?action=get_costcentercourses&costcenter="+costcentervalue,
            success: function(data){
                var template = '';
                $.each( data.courses, function( index, value) {
                    template += '<option value = ' + index + ' >' +value + '</option>';
                });
                $("#course_select").html(template);
                // var leveltemplate = '<option value=0>Select Courses</option>'
                var leveltemplate = ''
                $.each( data.levels, function( index, value) {
                    leveltemplate += '<option value = ' + index + ' >' +value + '</option>';
                });
                $("#level_select").html(leveltemplate);
            }
        });
    }
    return {
        load: function (){
        	$(document).on('change', '#costcenterselect', function() {
        		var costcentervalue = $(this).find("option:selected").val();
        		if (costcentervalue !== null) {
        			return new visualformchanges(costcentervalue);
        		}
        	});
    		// $(document).ready(function(){
    		// 	var costcentervalue = $('#costcenterselect').find("option:selected").val();
    		// 	if (costcentervalue !== null) {
      //   			return new visualformchanges(costcentervalue);
      //   		}
    		// });
        },
        init: function(args) {
        	console.log(args);
        	return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'deleteconfirm',
                component: 'block_gamification',
                param :args
            },
            {
                key: 'delete'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.CONFIRM,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[2]);
                    modal.getRoot().on(ModalEvents.yes, function(e) {
                        e.preventDefault();
                        $.ajax({
                        	method: "POST",
							dataType: "json",
							url: M.cfg.wwwroot + "/blocks/gamification/customajax.php?action=deletebadge&badgeid="+args.badgeid,
				      		success: function(data){
				      			window.location.href = window.location.href;
				      		}
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        }
    };
});