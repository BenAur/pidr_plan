function tib_help(event,room){
	//$(".zone-infos").filter(":visible").not("."+room).slideUp(100);
  $(".zone-infos h1").html(room);
  $(".zone-infos").css("left", get_left(room));
  $(".zone-infos").css("top", get_top(room));
  $(".zone-infos").slideDown(100);
  console.log(get_left(room) +";" + get_top(room));
}

function get_left(room){
  tableau=($("."+room).attr("coords")).split(",");
  return ((parseInt(tableau[0])+parseInt(tableau[2]))/2 - (parseInt($(".zone-infos").css("width"))/2));
}

function get_top(room){
  tableau=($("."+room).attr("coords")).split(",");
  return ((parseInt(tableau[1])+parseInt(tableau[3]))/2 - parseInt($(".zone-infos").css("height"))/2)
}

function get_room_number(a){
  tableau = $(a).attr("class").split(" ");
  console.log(tableau[1]);
  return parseInt(tableau[1]);
}

$(document).ready(function(){
  $(".room").on("click", function(e){tib_help(e,get_room_number(this))});
  //$(".zone-infos").on("click", function(e){$(".zone-infos").hide()});
});
