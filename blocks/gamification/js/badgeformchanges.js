
$("#badgegroup").select2({
	// placeholder: "Select bagde group",
});
$("#costcenter").select2();
$("#courseselectedit").select2({
                        // placeholder: "Select "+name,
});

function displaytextbox(){
// alert('points');
$('#displaypointsfield').css('display', 'block');
$('#displaycoursesfield').css('display', 'none');
$('#displaycourse').css('display', 'none');
$('#courseselecteditdisplay').css('display', 'none');
$('#displaypoints').css('display', 'block');
$('#radioerror').css('display', 'none');
$('.courseselect').css('display','none');
$('#courseserror').css('display', 'none');

}
function displayselectbox(){
// alert('course');
$('#displaycoursesfield').css('display', 'block');
$('#displaypointsfield').css('display', 'none');
$('#displaycourse').css('display', 'block');
$('#displaypoints').css('display', 'none');
$('#courseselecteditdisplay').css('display', 'block');
$('.courseselect').css('display','block');
$('#radioerror').css('display', 'none');
$('#pointserror').css('display', 'none');
}
// function getoptions(){
// 	// alert('badgegroupid');
// 	var data = document.getElementById('badgegroup').value;
// 	alert(data);
// }
// 
$(document).ready(function () {
	$("#badgegroup").on('change', function () {
  		var bgid = this.value;
        if (bgid !== null || bgid !== 'NULL') {         
            $.ajax({
                method: "GET",
                dataType: "json",
                url:"get_badgefieldinfo.php?value="+bgid,
                success: function (resp)    {
                	// console.log(resp);
                    var template = '';
                    var fail = '';
                    $.each( resp.data, function(index,value) {
                        template +=	'<option value = ' + index + ' >' +value + '</option>';
                    });var name = 'Course';
                    if(resp.event === 'ctc'){
                        var name = 'competencies'; 
		            	$(".courseorpointsdisplay").html('Competencies');
		            	// $('#placeholder').html('--Select competencies--');
		            } else if(resp.event === 'clc'){
                        var name = 'classrooms';
		            	$(".courseorpointsdisplay").html('Classrooms');
		            	// $('#placeholder').html('--Select classrooms--');
		            } else if(resp.event === 'lpc'){
                        var name = 'learning plans';
		            	$(".courseorpointsdisplay").html('Learning plans');
		            	// $('#placeholder').html('--Select learningplan--');
		            } else if(resp.event === 'certc'){
                        var name = 'Certification';
                        $('.courseorpointsdisplay').html('Certification');
                    }else if(resp.event === 'progc'){
                        var name = 'Program';
                        $('.courseorpointsdisplay').html('Program');
                    }
                    else if(resp.event === 'cc' || resp.event === 'ce'){
                        var name = 'Course';
                        $(".courseorpointsdisplay").html('Course');
                    }
                    // Dependency data in select.
		            $("#courseselect").html(template);
                    
            		$("#radiobuttons").css('display', 'block');
                    $("#radiobuttonsdisplay").css('display', 'block');
                    $("#courseselect").select2({
                        placeholder: "Select "+name,
                    });
                }
            });
        }
    });
});
$("#courseselect").select2({
    // placeholder: "Select "+name,
});

$('#pointsfield').change(function(){
    var points = this.value;
    // console.log(points);
    // alert(points);
    if(!points.match(/^\d+/)) {
        // alert(points);
        $('#pointserror').html('Points should be numeric');
        $('#pointserror').css('display', 'block');
    } else if (points.match(/^\d+/) && points == 0){
        $('#pointserror').html('Points should be greater than zero');
        $('#pointserror').css('display', 'block');
    } else if(points.match(/^\d+/) && points > 0) {
        $('#pointserror').css('display', 'none');
    } 
});
$("#badgegroup").on('change', function () {
   $("#badgename").on('change', function () { 
    $("#shortname").on('change', function () {
$('#page-blocks-gamification-addbadges #badgesubmitbutton').click(function(){
    // alert('submitted');
    var points = $('#pointsfield').val();
    var type = $('input[name="type"]:checked').val();
    var editcourses = $('#courseselectedit').val();
    var addcourses = $('#courseselect').val();
    var radio = $('#pointsradio').val();
    var image = $('#id_badgeimg').val();
    var is_true = 1;
    // var is_image = 0;

    if(type != 'course' && type != 'points'){
        // alert(type);
        // alert('mahesh');courseserror
        // alert(addcourses);
        // alert(editcourses);
        $('#radioerror').html('Select course or points');
        $('#radioerror').css('display', 'block');
        // return false;
        is_true = 0
    }
     if(type == 'course'){
        
        if(editcourses){
            var length = editcourses.length;
        } else if (addcourses){
            var length = addcourses.length;
        } else {
           var length = 0;
        }
        
        
        // alert(length);
        // alert(length);
        if(!length){
            // alert(length);
            $('#courseserror').html('Select atleast one course');
            $('#courseserror').css('display', 'block');
            // return false;
        is_true = 0
            
            // $('#badgesubmitbutton').event.preventDefault();
        }
    } else if (type == 'points') {
        if(!points){
            $('#pointserror').html('Points cannot be empty');
            $('#pointserror').css('display', 'block');
            // return false;
            is_true = 0
            
        }
        if(points <= 0 || !points.match(/^\d+/)){
            // alert(points);
            // return false;
            is_true = 0

            // $('#badgesubmitbutton').event.preventDefault();
            // alert(points);
        }
    } 
    var result = null;
    var is_exist = $.ajax({
                method: "GET",
                dataType: "json ",
                url:"get_imagepresent.php?image="+image,
                async: false,
                success: function (resp)    {
                    // console.log(resp);
                    result = resp;
                    if(!resp){
                        // $('#pointserror').html('Points cannot be empty');
                        // $('#pointserror').css('display', 'block');
                        // alert('no image');
                        return 0;
                    }
                    else {
                        return 1;
                    }

                }

            }).responseText;
    // 
    // if(!is_image){
    // alert('mahesh');

    console.log(is_exist);
    // alert(result);
    // alert('chandra');
    
    // console.log(is_exist);
        if(is_exist == 0){
            // alert('image not placed');
            $('#imagenotplacederror').css('display', 'block');
            $('#imagenotplacederror').html('image cannot be empty');
            is_true = 0

            // return false;
        }
        else {
            $('#imagenotplacederror').css('display', 'none');
            // alert('image placed');
        }
        
    // }
        // is_true = 0
    if(is_true === 0){
        return false;
    }
    // $("#id_badgeimg").on('change', function () {
   
    $('#courseserror').css('display', 'none');
    $('#radioerror').css('display', 'none');
    $('#imagenotplacederror').css('display', 'none');
    // alert('success');
    return true;
// });$('#pointserror').html('Points cannot be empty');
//     $('#pointserror').css('display', 'block');
//     alert('here');
//     return false;
});
});
});
});
// 
// 
// $('#badgegroup').on('change', function() {radiobuttonsdisplay
//   	var bgid = this.value;
//   	$.ajax({
//         type:"POST",
//         datatype:"JSON",
//         url:"get_badgefieldinfo.php?value="+bgid,
//         success: function(json_data) {
//             var temp = "";
//         	// var data = JSON.parse(json_data);
//         	console.log(data[0]);
//         	var records = JSON.parse(data[1]);
//         	console.log(records);
//         	$.each(records, function( key, value ) {
//                 temp += "<option value="+key+" > " +value+ "</option>";
//                 // console.log(key);
//                 // console.log(value);
//             });
//             // console.log(data.list);
//             console.log(temp);

//             $("#courseselect").html(temp);
//             $("#radiobuttons").css('display', 'block');
//             if(data[0] == 'ctc'){
//             	$("#courseorpointsdisplay").html('Competencies');
//             	$('#placeholder').html('--Select competencies--');
//             }
//             if(data[0] == 'iltc'){
//             	$("#courseorpointsdisplay").html('Classrooms');
//             	$('#placeholder').html('--Select classrooms--');
//             }
//             if(data[0] == 'lpc'){
//             	$("#courseorpointsdisplay").html('learning plans');
//             	$('#placeholder').html('--Select learningpla--');
//             }
//         }
//     });

// });
