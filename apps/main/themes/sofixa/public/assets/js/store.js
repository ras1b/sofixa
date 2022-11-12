var modalBox = $("#modalBox");
var openBuyModal = $(".openBuyModal");

openBuyModal.on("click", function() {
  var openBuyModal = $(this);
  var productID = openBuyModal.attr("product-id");
  $.ajax({
    type: "GET",
    url: "/apps/main/themes/sofixa/public/ajax/modal.php?action=buy&id=" + productID,
    success: function(result) {
      if (result == false) {
        swal.fire({
          title: lang.alert_title_error,
          text: lang.alert_message_something_went_wrong,
          type: "error",
          confirmButtonColor: "#02b875",
          confirmButtonText: lang.alert_btn_ok
        });
      }
      else {
       modalBox.html(result);
       $("#buyModal").modal("show");
      }
    }
  });
});
