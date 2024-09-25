define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/templates'],
        function ($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Templates) {
   	var blockContent = function(params){
   		var promise = Ajax.call([{
            methodname: 'block_gamifiaction_block_content',
            args: params
        }]);
        promise[0].done(function(resp){
        	Templates.render('block_gamification/block_view', resp).done(function(html) {
        		$('.gamification_content.leaderboard').html(html);
        		$('.leaderboard_content .loading').addClass('hidden');
        	});
        });
   	}
	return {
		blockContent : function(courseid){
			var block_view = $('.gamification_tabs').data('block_view');
			$(document).on('click', '.gamification_link', function(){
				if(!$(this).hasClass('active')){
					$('.leaderboard_content .loading').removeClass('hidden');
					$(this).addClass('active').siblings().removeClass('active');
					var data = $(this).data();
					var params = {};
					params.courseid = courseid;
					params.type = data.type;
					params.block_view = block_view;
					if(data.startdate == undefined){
						params.startdate = 0;	
					}else{
						params.startdate = data.startdate;
					}
					if(data.enddate == undefined){
						params.enddate = 0;	
					}else{
						params.enddate = data.enddate;
					}
					if(data.contextid == undefined){
						params.contextid = 1;	
					}else{
						params.contextid = data.contextid;
					}
					return new blockContent(params);
				}
			});
			var data = $('.gamification_tabs').find('.active').data();
			var params = {};
			params.courseid = courseid;
			params.type = data.type;
			params.block_view = block_view;
			if(data.startdate == undefined){
				params.startdate = 0;	
			}else{
				params.startdate = data.startdate;
			}
			if(data.enddate == undefined){
				params.enddate = 0;	
			}else{
				params.enddate = data.enddate;
			}
			if(data.contextid == undefined){
				params.contextid = 1;	
			}else{
				params.contextid = data.contextid;
			}
			return new blockContent(params);
		}
	}
});