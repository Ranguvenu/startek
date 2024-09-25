define(['jquery', 'jqueryui', 'block_training_calendar/moment', 'block_training_calendar/fullcalendar'], function ($, jqui, moment, fullCalendar) {
    var wwwroot = M.cfg.wwwroot;
    
    function initManage() {
        var sun = M.util.get_string('sun', 'block_my_event_calendar');
        var mon = M.util.get_string('mon', 'block_my_event_calendar');
        var tue = M.util.get_string('tue', 'block_my_event_calendar');
        var wed = M.util.get_string('wed', 'block_my_event_calendar');
        var thu = M.util.get_string('thu', 'block_my_event_calendar');
        var fri = M.util.get_string('fri', 'block_my_event_calendar');
        var sat = M.util.get_string('sat', 'block_my_event_calendar');
        var january = M.util.get_string('january', 'block_my_event_calendar'); 
        var february = M.util.get_string('february', 'block_my_event_calendar'); 
        var march = M.util.get_string('march', 'block_my_event_calendar'); 
        var april = M.util.get_string('april', 'block_my_event_calendar'); 
        var may = M.util.get_string('may', 'block_my_event_calendar'); 
        var june = M.util.get_string('june', 'block_my_event_calendar'); 
        var july = M.util.get_string('july', 'block_my_event_calendar'); 
        var august = M.util.get_string('august', 'block_my_event_calendar'); 
        var september = M.util.get_string('september', 'block_my_event_calendar'); 
        var october = M.util.get_string('october', 'block_my_event_calendar'); 
        var november = M.util.get_string('november', 'block_my_event_calendar'); 
        var december = M.util.get_string('december', 'block_my_event_calendar');
        $('#calendar').fullCalendar({  
        header: {
         left: 'prev',
         center: 'title',
         right: 'next'
        },
        dayNamesShort: [sun,mon,tue,wed,thu,fri,sat],
        monthNames: [january, february, march, april, may, june, july, august, september, october, november, december],
        weekends: true,
        slotDuration: '00:30:00',
        allDaySlot: false,        
        axisFormat: 'h:mm', 
        defaultDate: new Date(),
        selectable: true,
        defaultView: 'month',
        eventLimit: true,
        events: M.cfg.wwwroot +"/blocks/training_calendar/events.php",
        cache: true,        
        eventRender: function eventRender( event,element ) {
         var resp = '';
         resp += event.content;
         element.find('.fc-title').html(resp);
        return ['all', event.plugin].indexOf($('#id_eventtype').val()) >= 0;
        },
        loading: function (bool) {
         if (bool)
             $('#loading').show();
         else
             $('#loading').hide();
        }
        });

        $('#id_eventtype').on('change',function(){
          jq_conflict = jQuery.noConflict(false);
          jq_conflict("#id").hide();
          $('#calendar').fullCalendar('rerenderEvents');
          
       });
    }

    return {
        init: function () {
            initManage();
        }
    };
});