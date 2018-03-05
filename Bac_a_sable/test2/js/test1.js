//Merci ponpon
function tib_help(event,nom){
  event.preventDefault();
  $(".zone-infos").hide();
  $("."+nom).slideToggle(100);
}

$(document).ready(function(){
  //Ici on ajoute les éléments
  $(".sun").on("click", function(e){tib_help(e,"zsun")});
  $(".mercury").on("click", function(e){tib_help(e,"zmercury")});
  $(".venus").on("click", function(e){tib_help(e,"zvenus")});
  $(".zone-infos").on("click", function(e){$(".zone-infos").hide()});
});
