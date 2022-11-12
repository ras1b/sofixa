var helpVote = $("#help-vote");
var helpVoteBtn = $("#help-vote button");

helpVoteBtn.on("click", function() {
  var helpID = helpVote.data("id");
  var helpVoteValue = $(this).data("value");
  $.ajax({
    type: "GET",
    url: "/apps/main/public/ajax/help-vote.php?id=" + helpID + "&vote=" + helpVoteValue,
    success: function(result) {
      if (result == "success") {
        helpVote.html('<span class="text-success">' + lang.help_voting_success + '<span>');
      }
      else if (result == "error_login") {
        swal.fire({
          title: lang.alert_title_error,
          text: lang.alert_message_login,
          type: "error",
          confirmButtonColor: "#02b875",
          confirmButtonText: lang.alert_btn_ok
        });
      }
      else {
        swal.fire({
          title: lang.alert_title_error,
          text: lang.alert_message_something_went_wrong,
          type: "error",
          confirmButtonColor: "#02b875",
          confirmButtonText: lang.alert_btn_ok
        });
      }
    }
  });
});
