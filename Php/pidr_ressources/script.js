function tib_help(event,room){
	//$(".zone-infos").filter(":visible").not("."+room).slideUp(100);
  $(".zone-infos h1").html(room);
  $(".zone-infos p").html("% Chargement %");
  ajax_set_room_utilisateur(room);
  $("#pin").css("left", get_left(room));
  $("#pin").css("top", get_top(room));
  $("#pin").slideDown(100);
  //console.log(get_left(room) +";" + get_top(room));
}

function get_left(room){
  tableau=($("."+room).attr("coords")).split(",");
  return ((parseInt(tableau[0])+parseInt(tableau[2]))/2 - (parseInt($("#pin").css("width"))/2));
}

function get_top(room){
  tableau=($("."+room).attr("coords")).split(",");
  return ((parseInt(tableau[1])+parseInt(tableau[3]))/2 - parseInt($("#pin").css("height"))/2);
}

function get_room_number(a){
  tableau = $(a).attr("class").split(" ");
  //console.log(tableau[1]);
  return tableau[1];
}

function ajax_set_room_utilisateur(room){
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'pidr_room_request.php');
  var form = new FormData();
  console.log("ajax room : "+ room);
  form.append('room_number', room);
  //Definition de la fonction qui traite les boutons
  xhr.onreadystatechange = function (event) {
    // XMLHttpRequest.DONE === 4
    if (this.readyState === XMLHttpRequest.OPENED) {
      $(".zone-infos p").html("Uploading");
    }
    if (this.readyState === XMLHttpRequest.DONE) {
      console.log(xhr.responseText);
      $(".zone-infos p").html(xhr.responseText);     
      //$(".zone-infos").css("height","500px");   
    }
  }
  xhr.send(form);
}
  

$(document).ready(function(){
  $(".room").on("click", function(e){tib_help(e,get_room_number(this))});
  $(".b4").on("click",function(e){
    if ($(".plan4").css("display")=="none"){
      $(".plan5").css("display","none");
      $(".plan6").css("display","none");
      $(".plan4").css("display","inline");
      //$(".zone-infos").slideUp(100);
    }
  })
  $(".b5").on("click",function(e){
    if ($(".plan5").css("display")=="none"){
      $(".plan4").css("display","none");
      $(".plan6").css("display","none");
      $(".plan5").css("display","inline");
      //$(".zone-infos").slideUp(100);
    }
  })
  $(".b6").on("click",function(e){
    if ($(".plan4").css("display")=="none"){
      $(".plan5").css("display","none");
      $(".plan4").css("display","none");
      $(".plan6").css("display","inline");
      //$(".zone-infos").slideUp(100);
    }
  })
  //$(".zone-infos").on("click", function(e){$(".zone-infos").hide()});
  
  //TODO faire en sorte de n'afficher les sujets de thèse que lorsque l'on clique sur sujet de thèse
  //TODO rendre propre le cadre de droite

});
