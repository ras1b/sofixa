
var deliverButton = $(".deliverButton");

deliverButton.on("click", function() {
  var chestID = $(this).attr('data-chest');
  swal.fire({
    title: lang.alert_title_warning,
    text: lang.alert_message_chest_confirm,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#02b875",
    cancelButtonColor: "#f5365c",
    cancelButtonText: lang.alert_btn_cancel,
    confirmButtonText: lang.alert_btn_confirm,
    reverseButtons: true
  }).then(function(isAccepted) {
    if (isAccepted.value) {
      swal.fire({
        title: lang.alert_title_warning,
        html: lang.alert_message_chest_sending,
        type: "warning",
        allowOutsideClick: false,
        showConfirmButton: false
      });
      $.ajax({
        type: "POST",
        url: "/apps/main/public/ajax/chest.php",
        data: {chestID: chestID},
        success: function(result) {
          if (result == "error") {
            swal.fire({
              title: lang.alert_title_error,
              text: lang.alert_message_something_went_wrong,
              type: "error",
              confirmButtonColor: "#02b875",
              confirmButtonText: lang.alert_btn_ok
            });
          }
          else if (result == "error_login") {
            swal.fire({
              title: lang.alert_title_error,
              text: "Please login to open the chest.",
              type: "error",
              confirmButtonColor: "#02b875",
              confirmButtonText: lang.alert_btn_ok
            }).then(function() {
              window.location = '/login';
            });
          }
          else if (result == "error_connection") {
            swal.fire({
              title: lang.alert_title_error,
              text: lang.alert_message_chest_server_error,
              type: "error",
              confirmButtonColor: "#02b875",
              confirmButtonText: lang.alert_btn_ok
            });
          }
          else {
            swal.fire({
              title: lang.alert_title_success,
              text: lang.alert_message_chest_sent,
              type: "success",
              confirmButtonColor: "#02b875",
              confirmButtonText: lang.alert_btn_ok
            }).then(function() {
              window.location = '/chest';
            });
          }
        }
      });
    }
  });
});
