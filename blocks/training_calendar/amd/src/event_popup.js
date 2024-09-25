/**
 * Add a create new event modal to the page.
 *
 * @module     blocks/training calendar
 * @package    calendar
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function($, Str, ModalFactory, ModalEvents) {

    return  {
        popup_eventinfo: function(args){
            customstrings = Str.get_strings([{
                        key: 'when',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'openson',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'closeson',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'eventtype',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'summary'
                    },
                    {
                        key: 'trainers',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'location',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'enrol',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'request',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'processing',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'waiting',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'notpublished',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'launch',
                        component: 'block_training_calendar'
                    },
                    {
                        key: 'view',
                    }
                    ]);

        return customstrings.then(function(strings) {
                var btn = '';
                var event_details = '';
                var other_details = 1;
                var pop_desc =  args.eventlocal_eventname;
                if (args.eventeventtype == "session_open" || args.eventeventtype == "open") {
                    if (args.eventlocal_eventenddate == 'null') {
                         event_details += '<div class="row  mt-10 mb-10"><div class="col-xs-1"> <i class="icon fa fa-clock-o fa-fw " aria-hidden="true" title="'+strings[0]+'" aria-label="When"></i> </div><div class="col-xs-11"> '+strings[1] + args.eventlocal_eventstartdate+ '</div></div>';
                    } else {
                        event_details += '<div class="row  mt-10 mb-10"><div class="col-xs-1"> <i class="icon fa fa-clock-o fa-fw " aria-hidden="true" title="'+strings[0]+'" aria-label="When"></i> </div><div class="col-xs-11">' + args.eventlocal_eventstartdate+ ' - '+ args.eventlocal_eventenddate+ '</div></div>';
                    }
                } else if (args.eventeventtype == "session_close" || args.eventeventtype == "close") {
                    event_details += '<div class="row  mt-10 mb-10"><div class="col-xs-1"> <i class="icon fa fa-clock-o fa-fw " aria-hidden="true" title="'+strings[0]+'" aria-label="When"></i> </div><div class="col-xs-11"> '+strings[2] + args.eventlocal_eventenddate+ '</div></div>';
                } else { // all other events like course site mod
                    event_details += '<div class="row  mt-10 mb-10"><div class="col-xs-1"> <i class="icon fa fa-clock-o fa-fw " aria-hidden="true" title="'+strings[0]+'" aria-label="When"></i> </div><div class="col-xs-11"> ' + args.eventlocal_eventstartdate+ '</div></div>';
                    event_details += '<div class="row  mt-10 mb-10"><div class="col-xs-1"> <i class="icon fa fa-calendar fa-fw  " aria-hidden="true" title="'+strings[3]+'" aria-label="Event type"></i></i> </div><div class="col-xs-11">' + args.eventeventtype + '</div></div>';
                    event_details += '<div class="row  mt-10 mb-10"><div class="col-xs-1"> <i class="icon fa fa-align-left fa-fw " aria-hidden="true" title="'+strings[4]+'" aria-label="Summary"></i></i> </div><div class="col-xs-11">' + args.eventsummary + '</div></div>';
                    other_details = 0;
                }
                if (other_details == 1) {
                    event_details += '<div class="row  mt-10 mb-10"><div class="col-xs-1"> <i class="icon fa fa-align-left fa-fw " aria-hidden="true" title="'+strings[5]+'" aria-label="Trainers"></i></i> </div><div class="col-xs-11">' + args.eventtrainer + '</div></div>';
                    event_details += '<div class="row  mt-10 mb-10"><div class="col-xs-1"> <i class="icon fa fa-map-marker fa-fw " aria-hidden="true" title="'+strings[6]+'" aria-label="Location"></i> </div><div class="col-xs-11">' + args.eventlocation + '</div></div>';
                }

                btn += '<ul class="eventpopup_footer">';
                switch (args.eventplugin) {
                    case 'local_classroom':
                        if((args.eventenrolled == false) && (args.eventself_enrol == true)) {
                            var button_classroom = function () {
                                var tmp = null;
                                $.ajax({
                                    async: false,
                                    type: "POST",
                                    global: false,
                                    dataType: "json",
                                    url: M.cfg.wwwroot + '/blocks/training_calendar/ajax.php?instance='+args.eventinstance+'&plugin=local_classroom',
                                    success: function (returndata) {
                                        if ((returndata == 1)) {
                                          tmp = '<li><a href= "javascript:void(0)" onclick="(function(e){ require(\'local_classroom/classroom\').ManageclassroomStatus({action:\'selfenrol\', id: '+args.eventinstance+', classroomid:'+args.eventinstance+',actionstatusmsg:\'classroom_self_enrolment\',classroomname:\''+args.eventlocal_eventname+'\'}) })(event)"><button class="btn">'+strings[7]+'</button></a> </li>';
                                        }else if ((returndata == 2)) {
                                          tmp = '<li><a href= "javascript:void(0)" onclick="(function(e){ require(\'local_classroom/classroom\').ManageclassroomStatus({action:\'enrolrequest\', id: '+args.eventinstance+', classroomid:'+args.eventinstance+',actionstatusmsg:\'classroom_enrolrequest_enrolment\',classroomname:\''+args.eventlocal_eventname+'\'}) })(event)"><button class="btn">'+strings[8]+'</button></a> </li>';
                                        }else if ((returndata == 3)) {
                                          tmp = '<li><button class="btn">'+strings[9]+'</button></li>';
                                        }else if ((returndata == 4)) {
                                          tmp = '<li><button class="btn">'+strings[10]+'</button></li>';
                                        } else {
                                           tmp = '<li>'+strings[11]+'</li>';
                                        }
                                    }
                                });
                                return tmp;
                            }();
                            btn += button_classroom;

                        }

                        else if(args.eventenrolled == true) {
                           btn += '<li><a href= "' + M.cfg.wwwroot + '/local/classroom/view.php?cid=' + args.eventinstance +'" target="_blank"><button class="btn">'+strings[12]+'</button></a> </li>';
                        }
                    break;
                    case 'local_program':
                        if((args.eventenrolled == false) && (args.eventself_enrol == true)) {
                            var button_program = function () {
                                var tmp = null;
                                $.ajax({
                                    async: false,
                                    type: "POST",
                                    global: false,
                                    dataType: "json",
                                    url: M.cfg.wwwroot + '/blocks/training_calendar/ajax.php?instance='+args.eventinstance+'&plugin=local_program',
                                    success: function (returndata) {
                                        if ((returndata == 1)) {
                                          tmp = '<li><a href= "javascript:void(0)" onclick="(function(e){ require(\'local_program/program\').ManageprogramStatus({action:\'selfenrol\', id: '+args.eventinstance+', programid:'+args.eventinstance+',actionstatusmsg:\'program_self_enrolment\',programname:\''+args.eventlocal_eventname+'\'}) })(event)"><button class="btn">'+strings[7]+'</button></a> </li>';
                                        } else {
                                           tmp = '<li>'+strings[11]+'</li>';
                                        }
                                    }
                                });
                                return tmp;
                            }();
                            btn += button_program;
                        }

                        else if(args.eventenrolled == true)
                        btn += '<li><a href= "' + M.cfg.wwwroot + '/local/program/view.php?bcid=' + args.eventinstance +'" target="_blank"><button class="btn">'+strings[12]+'</button></a> </li>';
                    break;
                    case 'local_certification':
                        if((args.eventenrolled == false) && (args.eventself_enrol == true)) {
                            var button_certification = function () {
                                var tmp = null;
                                $.ajax({
                                    async: false,
                                    type: "POST",
                                    global: false,
                                    dataType: "json",
                                    url: M.cfg.wwwroot + '/blocks/training_calendar/ajax.php?instance='+args.eventinstance+'&plugin=local_certification',
                                    success: function (returndata) {
                                        if ((returndata == 1)) {
                                          tmp = '<li><a href= "javascript:void(0)"  onclick="(function(e){ require(\'local_certification/certification\').ManagecertificationStatus({action:\'selfenrol\', id: '+args.eventinstance+', certificationid:'+args.eventinstance+',actionstatusmsg:\'certification_self_enrolment\',certificationname:\''+args.eventlocal_eventname+'\'}) })(event)"><button class="btn">'+strings[7]+'</button></a> </li>';
                                        } else {
                                           tmp = '<li>'+strings[11]+'</li>';
                                        }
                                    }
                                });
                                return tmp;
                            }();
                            btn += button_certification;
                        }

                        else if(args.eventenrolled == true)
                        btn += '<li><a href= "' + M.cfg.wwwroot + '/local/certification/view.php?ctid=' + args.eventinstance +'" target="_blank"><button class="btn">'+strings[12]+'</button></a> </li>';
                    break;
                    case 'course':
                        if((args.eventenrolled == false) && (args.eventself_enrol == true)) {
                            var button_course = function () {
                                var tmp = null;
                                $.ajax({
                                    async: false,
                                    type: "POST",
                                    global: false,
                                    dataType: "json",
                                    url: M.cfg.wwwroot + '/blocks/training_calendar/ajax.php?instance='+args.eventinstance+'&plugin=local_certification',
                                    success: function (returndata) {
                                        if ((returndata == 1)) {
                                          tmp = '<li><a href= "' + M.cfg.wwwroot + '/course/view.php?id=' + args.eventinstance +'" target="_blank"><button class="btn">'+strings[13]+'</button></a> </li>';
                                        } else {
                                           tmp = '<li>'+strings[11]+'</li>';
                                        }
                                    }
                                });
                                return tmp;
                            }();
                            btn += button_course;
                        }
                        else if(args.eventenrolled == true)
                        btn += '<li><a href= "' + M.cfg.wwwroot + '/course/view.php?id=' + args.eventinstance +'" ><button class="btn">'+strings[13]+'</button></a> </li>';
                    break;
                    default:
                        btn += '<li><a href="'+ M.cfg.wwwroot+'" target="_blank"></li>';
                    break;
                }
                btn += '</ul>';
                ModalFactory.create({
                    title: pop_desc,
                    body: event_details,
                    footer: btn
                  }).done(function(modal) {
                    modal.show();
                    modal.getRoot().click(function(e) {
                        modal.show();
                    }.bind(this));
                    $(".close").click(function(e) {
                        modal.hide();
                        modal.destroy();
                    }.bind(this));
                });
            }.bind(this));
        },
        load: function () {
           // do nothing
        }
    };
});