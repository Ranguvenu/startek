/**
 * Add a create new group modal to the page.
 *
 * @module     core_group/AjaxForms
 * @class      AjaxForms
 * @package    core_group
 * @copyright  2017 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'local_certification/jquery.dataTables',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'jquery',
    'jqueryui'
], function(dataTable, Str, ModalFactory, ModalEvents, Ajax, $) {
    var certification;
    return certification = {
        init: function(args) {
            // this.certificationDatatable(args);
            // this.SessionDatatable(args);
            this.AssignUsers(args);
        },
        certificationDatatable: function(args) {
            params = [];
            params.action = 'viewcertifications';
            params.certificationstatus = args.certificationstatus;
			Str.get_string('search','local_program').then(function(s) {
            var oTable = $('#viewcertifications').dataTable({
                'bInfo': false,
                'processing': true,
                'serverSide': true,
                'ajax': {
                    "type": "POST",
                    "url": M.cfg.wwwroot + '/local/certification/ajax.php',
                    "data": params
                },
                // "responsive": true,
                // "language": {
                //     "processing": "DataTables is currently busy."
                // },
                "bLengthChange": false,
                "language": {
                    "paginate": {
                        "next": ">",
                        "previous": "<"
                    },
                    'processing': '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>'
                },
				 "oLanguage": {
					"sSearch": s
				 },
                "pageLength": 6
                //"lengthMenu": [
                //    [4, 10, 50, 100, -1],
                //    ["Show 2", "Show 5", "Show 25", "Show 50", "Show All"]
                //]
            });
			});
            
        },
        SessionDatatable: function(args) {
            var strings = Str.get_strings([{
                key: 'search',
                component: 'moodle'
            }]).then(function (str){
            params = [];
            params.action = 'viewcertificationsessions';
            params.certificationid = $('#viewcertificationsessions').data('certificationid');
            var oTable = $('#viewcertificationsessions').dataTable({
                'processing': true,
                'serverSide': true,
                "language": {
                    "paginate": {
                        "next": ">",
                        "previous": "<"
                    },
                    'processing': '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>',
                    "search": "",
                    "searchPlaceholder": str[0]
                },
                'ajax': {
                    "type": "POST",
                    "url": M.cfg.wwwroot + '/local/certification/ajax.php',
                    "data":params
                },
                "responsive": true,
                "pageLength": 5,
                "bLengthChange": false,
                "bInfo" : false,
            });
            }.bind(this));
        },
        CoursesDatatable: function(args) {
            var strings = Str.get_strings([{
                key: 'search',
                component: 'moodle'
            }]).then(function (str){
                params = [];
                params.action = 'viewcertificationcourses';
                params.certificationid = $('#viewcertificationcourses').data('certificationid');
                var oTable = $('#viewcertificationcourses').dataTable({
                    'processing': true,
                    'serverSide': true,
                    "language": {
                        "paginate": {
                            "next": ">",
                            "previous": "<"
                        },
                        'processing': '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>',
                        "search": "",
                        "searchPlaceholder": str[0]
                    },
                    'ajax': {
                        "type": "POST",
                        "url": M.cfg.wwwroot + '/local/certification/ajax.php',
                        "data":params
                    },
                    "responsive": true,
                    "pageLength": 5,
                    "bLengthChange": false,
                    "bInfo" : false,
                });
            }.bind(this));
        },
        UsersDatatable: function(args) {
            var strings = Str.get_strings([{
                key: 'search',
                component: 'moodle'
            }]).then(function (str){
                params = [];
                params.action = 'viewcertificationusers';
                params.certificationid = $('#viewcertificationusers').data('certificationid');
                var oTable = $('#viewcertificationusers').dataTable({
                    'processing': true,
                    'serverSide': true,
                    "language": {
                        "paginate": {
                            "next": ">",
                            "previous": "<"
                        },
                        'processing': '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>',
                        "search": "",
                        "searchPlaceholder": s[0]
                    },
                    'ajax': {
                        "type": "POST",
                        "url": M.cfg.wwwroot + '/local/certification/ajax.php',
                        "data":params
                    },
                    "responsive": true,
                    "pageLength": 5,
                    "bLengthChange": false,
                    "bInfo" : false,
                });
            }.bind(this));
        },
        EvaluationsDatatable: function(args) {
            var strings = Str.get_strings([{
                key: 'norecordsfound',
                component: 'local_certification'
            }]).then(function (str){
                $('#viewevaluations').dataTable({
                    searching: true,
                    bLengthChange: false,
                    bInfo : false,
                    lengthMenu: [5, 10, 25, 50, -1],
                       'aaSorting': [],
                        'language': {
                              'emptyTable': s[0],
                                'paginate': {
                                            'previous': '<',
                                            'next': '>'
                                        },
                                'processing': '<img src='+M.cfg.wwwroot + '/local/ajax-loader.svg>'
                             },
                });
            }.bind(this));
        },
        deleteConfirm: function(args) {
            console.log(args);
            return Str.get_strings([{
                key: 'confirmation',
                component: 'local_certification'
            },
            {
                key: 'deleteconfirm',
                component: 'local_certification',
                param: args.certificationname,
            },
            {
                key: 'deleteallconfirm',
                component: 'local_certification'
            },
            {
                key: 'yes'
            },
            {
                key: 'deletecourseconfirm',
                component: 'local_certification'
            }
            ]).then(function(s) {
                 if(args.action=="deletecertification"){
                    s[1]=s[1];
                 }
                 if(args.action=="deletecertificationcourse"){
                    s[1]=s[4];
                 }else{
                     s[1]=s[2];
                 }
                ModalFactory.create({
                   
                    title: s[0],
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[3]);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_certification_' + args.action,
                            args: args
                        }]);
                        promise[0].done(function(resp) {
                            window.location.href = M.cfg.wwwroot + '/local/certification/view.php?ctid='+args.certificationid;
                        }).fail(function(ex) {
                            // do something with the exception
                             console.log(ex);
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        AssignUsers: function(args) {
            $('.usersselect').click(function() {
                var type = $(this).data('id');

                if (type === 'removeselect') {
                    $('input#remove').prop('disabled', false);
                    $('input#add').prop('disabled', true);
                } else if (type === 'addselect') {
                    $('input#remove').prop('disabled', true);
                    $('input#add').prop('disabled', false);
                }

                if ($(this).hasClass('select_all')) {
                    $('#' + type + ' option').prop('selected', true);
                } else if ($(this).hasClass('remove_all')) {
                    $('#' + type ).val('').trigger("change");
                }
            });
        },
        certificationStatus: function(args) {
            return Str.get_strings([
            {
                key: 'confirmation',
                component: 'local_certification'
            },
            {
                key: args.actionstatusmsg,
                component: 'local_certification'
            },
            {
                key: 'yes'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[2]);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_certification_' + args.action,
                            args: args
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
        ManagecertificationStatus: function(args) {
            return Str.get_strings([{
                key: 'confirmation',
                component: 'local_certification'
            },
            {
                key: args.actionstatusmsg,
                component: 'local_certification',
                param: args.certificationname,
            },
            {
                key: 'deleteallconfirm',
                component: 'local_certification'
            },
            {
                key: 'yes'
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[3]);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'local_certification_managecertificationStatus',
                            args: args
                        }]);
                        promise[0].done(function(resp) {
                            window.location.href = M.cfg.wwwroot + '/local/certification/view.php?ctid='+args.certificationid;
                        }).fail(function(ex) {
                            // do something with the exception
                             console.log(ex);
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        unEnrolUser : function(args){
            return Str.get_strings([{
                key: 'confirm'
            },
            {
                key: 'certification_self_unenrolment',
                component: 'local_certification',
                param :args.certificationname
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: s[1]
                }).done(function(modal) {
                    this.modal = modal;
                    modal.setSaveButtonText(s[0]);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        var params = {};
                        params.userid = args.userid;
                        params.certificationid = args.certificationid;
                        params.contextid = args.contextid;
                        var promise = Ajax.call([{
                            methodname: 'local_certification_deleteuser',
                            args: params
                        }]);
                        promise[0].done(function(resp) {
                            window.location.href = M.cfg.wwwroot;
                        }).fail(function(ex) {
                             console.log(ex);
                        });
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        load: function () {

        }
    };
});  