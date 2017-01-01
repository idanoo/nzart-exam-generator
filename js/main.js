$(document).ready(function(){
  $('#cover').on("click", function(){
    $(this).hide();
    $("#login").hide();
  });

  $("#loginTrigger").on("click", function(){
      $("#cover").show();
      $("#login").show();
  });
});