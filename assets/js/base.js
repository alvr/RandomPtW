$("#randomForm").submit(function(event) {
  search(event);
});

$("#rollAgain").click(function (event) {
  search(event);
});

var search = function (event) {
  event.preventDefault();
  $("#loading").css('display', 'block');
  var userValue = $("#username").val();
  $.ajax({
    type: "post",
    url: "random.php",
    dataType: "json",
    data: { username: userValue },
    success: function (response) {
      $("#loading").hide();
      var title = response["title"];
      var url = "https://myanimelist.net/anime/" + response["id"];
      var desc = response["desc"];
      var poster = response["poster"];
      if(title === null) {
        $("#resultTitle").text("Invalid User");
        $("#resultDesc").hide();
        $("#resultButtons").hide();
        $(".ptw-image").css("cssText", "background-color: red !important;");
      } else {
        $("#resultTitle").text(title);
        $("#resultDesc").html(desc).show();
        $("#resultButtons").show();
        $("#resultInfo").attr("href", url).attr("target", "_blank");
        $(".ptw-image").css('background', "url(" + poster + ") center / cover");
      }
      $('#resultCard').show().animate({ opacity: 1, top: "-10px" }, 'slow').scrollintoview({ duration: "slow" });
    }
  });
};