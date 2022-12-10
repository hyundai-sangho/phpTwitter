let userLoggedIn = document.querySelector("#profile").dataset["userLoggedIn"];
let profileUsername = document.querySelector("#profile").dataset["profileUsername"];

$(document).ready(function () {
  $("#loading").show();

  // 첫 번째 게시물 로드에 대한 원본 Ajax 요청
  $.ajax({
    url: "includes/handlers/ajax_load_profile_posts.php",
    type: "POST",
    data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
    cache: false,

    success: function (data) {
      $("#loading").hide();
      $(".posts_area").html(data);

      console.log(data);
    },
  });

  $(window).scroll(function () {
    let page = $(".posts_area").find(".nextPage").val();
    let noMorePosts = $(".posts_area").find(".noMorePosts").val();

    if (document.body.scrollHeight == window.scrollY + window.innerHeight && noMorePosts == "false") {
      $("#loading").show();

      let ajaxReq = $.ajax({
        url: "includes/handlers/ajax_load_profile_posts.php",
        type: "POST",
        data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
        cache: false,

        success: function (response) {
          $(".posts_area").find(".nextPage").remove();
          $(".posts_area").find(".noMorePosts").remove();

          $("#loading").hide();

          console.log(response)

          // 화면 리로드시에 '더 이상 표시할 게시물이 없습니다.'가 화면에 2번 나오는 것을 방지
          if ($(".posts_area > p").text() !== "더 이상 표시할 게시물이 없습니다. ") {
            $(".posts_area").append(response);
          }
        },
      });
    }

    return false;
  });
});
