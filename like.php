<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="/sns/assets/css/style.css">
</head>

<body>

  <style>
    body {
      background-color: #fff;
    }

    form {
      position: absolute;
      top: 0;
    }
  </style>

  <?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';                 // maria DB 연결
  include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/User.php';         // 사용자 관련 클래스
  include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Post.php';         // 게시물 관련 클래스
  include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Notification.php'; // 알림 관련 클래스

  if (isset($_SESSION['username'])) {
    /** 현재 로그인한 계정 사용자 */
    $userLoggedIn = $_SESSION['username'];
    $userDetailsQuery = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
    $user = mysqli_fetch_array($userDetailsQuery);
  } else {
    header('Location: register.php');
  }

  // 게시물의 ID를 얻기
  if (isset($_GET['post_id'])) {
    $postId = $_GET['post_id'];
  }

  $getLikes = mysqli_query($con, "SELECT likes, added_by FROM posts WHERE id='$postId'");
  $row = mysqli_fetch_array($getLikes);
  $totalLikes = $row['likes'];
  $userLiked = $row['added_by'];

  $userDetailsQuery = mysqli_query($con, "SELECT * FROM users WHERE username='$userLiked'");
  $row = mysqli_fetch_array($userDetailsQuery);
  $totalUserLikes = $row['num_likes'];

  // 좋아요 버튼
  if (isset($_POST['like_button'])) {
    $totalLikes++;
    $updateLikeButtonQuery = mysqli_query($con, "UPDATE posts SET likes='$totalLikes' WHERE id='$postId'");
    $totalUserLikes++;
    $userLikes = mysqli_query($con, "UPDATE users SET num_likes = '$totalUserLikes' WHERE username='$userLiked'");
    $insertUserQuery = mysqli_query($con, "INSERT INTO likes VALUES('', '$userLoggedIn', '$postId')");

    // 알림 추가
    if ($userLiked != $userLoggedIn) {
      $notification = new Notification($con, $userLoggedIn);
      $notification->insertNotification($postId, $userLiked, "like");
    }
  }

  // 싫어요 버튼
  if (isset($_POST['unlike_button'])) {
    $totalLikes--;
    $updateLikeButtonQuery = mysqli_query($con, "UPDATE posts SET likes='$totalLikes' WHERE id='$postId'");
    $totalUserLikes--;
    $userLikes = mysqli_query($con, "UPDATE users SET num_likes = '$totalUserLikes' WHERE username='$userLiked'");
    $insertUserQuery = mysqli_query($con, "DELETE FROM likes WHERE username='$userLoggedIn' AND post_id='$postId'");
  }


  // 좋아요 받은 갯수 확인
  $checkQuery = mysqli_query($con, "SELECT * FROM likes WHERE username='$userLoggedIn' AND post_id='$postId'");
  $numRows = mysqli_num_rows($checkQuery);

  if ($numRows > 0) {
    echo
    "<form action='like.php?post_id=$postId' method='POST'>
        <input type='submit' class='comment_like' name='unlike_button' value='싫어요'>
        <div class='like_value'>
          $totalLikes 좋아요
        </div>
      </form>
      ";
  } else {
    echo
    "<form action='like.php?post_id=$postId' method='POST'>
      <input type='submit' class='comment_like' name='like_button' value='좋아요'>
      <div class='like_value'>
        $totalLikes 좋아요
      </div>
    </form>
  ";
  }

  ?>
</body>

</html>
