$("#costcenter").select2({
});
$(document).on('change', '#costcenter', function() {
	var costcenterselected = $(this).find("option:selected").val();
	if (costcenterselected !== null) {
		$.ajax({
			method: "POST",
			dataType: "json",
			url: M.cfg.wwwroot + "/blocks/gamification/get_badgefieldinfo.php?costcenter="+costcenterselected,
			success: function(data){
				$.each(data, function(event,value){
					var eventid = value.eventid;
					var points = value.value;
					var active = value.active;
					var badgeactive = value.badgeactive;
					if(active == 1){
						$("#id_events"+eventid+"_event").attr('checked', true);
					}else{
						$("#id_events"+eventid+"_event").attr('checked', false);
					}
					if(points > 0){
						$("#id_events"+eventid+"_eventname").val(points);
					}else{
						$("#id_events"+eventid+"_eventname").val('');
					}
					if(badgeactive == 1){
						$("#id_group"+eventid+"_badgegroup").attr('checked', true);
					}else{
						$("#id_group"+eventid+"_badgegroup").attr('checked', false);
					}
				});
			}
		});
	}
});