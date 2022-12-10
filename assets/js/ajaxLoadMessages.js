let user = document.querySelector("#dropdownDataWindow").dataset["userloggedin"];

$(document).ready(function () {
  $(".dropdown_data_window").scroll(function () {
    let innerHeight = $(".dropdown_data_window").innerHeight();
    let scrollTop = $(".dropdown_data_window").scrollTop();
    let page = $(".dropdown_data_window").find(".nextPageDropdownData").val();
    let noMoreData = $(".dropdown_data_window").find(".noMoreDropdownData").val();

    if (scrollTop + innerHeight >= $(".dropdown_data_window")[0].scrollHeight && noMoreData == "false") {
      let pageName; // Ajax 요청을 보낼 페이지의 이름을 보유
      let type = $("#dropdown_data_type").val();

      if (type == "notification") {
        pageName = "ajax_load_notifications.php";
      } else if ((type = "message")) {
        pageName = "ajax_load_messages.php";
      }

      let ajaxReq = $.ajax({
        url: "includes/handlers/" + pageName,
        type: "POST",
        data: "page=" + page + "&userLoggedIn=" + user,
        cache: false,

        success: function (response) {
          $(".dropdown_data_window").find(".nextPageDropdownData").remove();
          $(".dropdown_data_window").find(".noMoreDropdownData").remove();

          $(".dropdown_data_window").append(response);
        },
      });
    }

    return false;
  });
});
