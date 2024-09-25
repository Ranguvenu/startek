new DG.OnOffSwitch({
    el: "#on-off-switch",
    textOn: "On",
    textOff: "Off",
    listener:function(name, checked){
        if(checked){
            $("#id_pointsheader :input").prop("disabled", false);
        }
        else{
            $("#id_pointsheader :input").prop("disabled", true);
        }
        $("#on-off-switch").prop('checked',false);
        $("#listener-text").html("Listener called for " + name + ", checked: " + checked);
    }
});
new DG.OnOffSwitch({
    el: "#on-off-switch1",
    textOn: "On",
    textOff: "Off",
    listener:function(name, checked){
        if(checked){
            $("#id_badgeheader :input").prop("disabled", false);
        }
        else{
            $("#id_badgeheader :input").prop("disabled", true);
        }
        $(name).prop('checked',false);
        $("#listener-text").html("Listener called for " + name + ", checked: " + checked);
    }
});
function disableform(){
    $("#id_pointsheader :input").prop("disabled", true);
    $("#id_badgeheader :input").prop("disabled", true);
    $(".on-off-switch1").hide();
    $(".on-off-switch").hide();
}
function enableform(){
    $("#id_pointsheader :input").prop("disabled", false);
    $("#id_badgeheader :input").prop("disabled", false);
    $(".on-off-switch1").show();
    $(".on-off-switch").show();
}
