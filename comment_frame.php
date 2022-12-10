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

?>

<!DOCTYPE html>
<html lang="ko">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <link rel="stylesheet" href="assets/css/style.css">

  <style>
    * {
      font-size: 12px;
    }
  </style>
</head>

<body>

  <?php
  if (isset($_GET['post_id'])) {
    /** 댓글 작성자 ID */
    $postId = $_GET['post_id'];
  }

  $userQuery = mysqli_query($con, "SELECT added_by, user_to FROM posts WHERE id = '$postId'");
  $row = mysqli_fetch_array($userQuery);

  /** 댓글 작성자 */
  $postedTo = $row['added_by'];
  $userTo = $row['user_to'];

  if (isset($_POST['postComment' . $postId]) && trim($_POST['post_body']) !== '') {
    $postBody = $_POST['post_body'];
    $postBody = mysqli_escape_string($con, $postBody); // 댓글 내용
    $dateTimeNow = date('y-m-d h:i:s');                       // 댓글 작성 시간

    /** comments 테이블에 댓글 관련 내용 입력 쿼리문 */
    $insertPost = mysqli_query(
      $con,
      "INSERT INTO `comments` (`id`, `post_body`, `posted_by`, `posted_to`, `date_added`, `removed`, `post_id`)
    VALUES ('', '$postBody', '$userLoggedIn', '$postedTo', '$dateTimeNow', 'no', '$postId');"
    );

    if ($postedTo != $userLoggedIn) {
      $notification = new Notification($con, $userLoggedIn);
      $notification->insertNotification($postId, $postedTo, "comment");
    }

    if ($userTo != 'none' && $userTo != $userLoggedIn) {
      $notification = new Notification($con, $userLoggedIn);
      $notification->insertNotification($postId, $userTo, "profile_comment");
    }

    $getCommenters = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$postId'");
    $notifiedUsers = array();

    while ($row = mysqli_fetch_array($getCommenters)) {
      if ($row['posted_by'] != $postedTo && $row['posted_by'] != $userTo && $row['posted_by'] != $userLoggedIn && !in_array($row['posted_by'], $notifiedUsers)) {
        $notification = new Notification($con, $userLoggedIn);
        $notification->insertNotification($postId, $row['posted_by'], "comment_non_owner");

        array_push($notifiedUsers, $row['posted_by']);
      }
    }

    echo "<p>댓글 게시!!</p>";
  }

  ?>

  <form action="comment_frame.php?post_id=<?= $postId ?>" id="comment_form" name="postComment<?= $postId ?>" method="POST">
    <textarea name="post_body"></textarea>
    <input type="submit" name="postComment<?= $postId; ?>" value="입력">
  </form>

  <!-- 댓글 로드 -->
  <?php
  $getComments = mysqli_query($con, "SELECT * FROM comments WHERE post_id = '$postId' ORDER BY id ASC");
  $count = mysqli_num_rows($getComments);

  if ($count != 0) {
    while ($comment = mysqli_fetch_array($getComments)) {
      $commentBody = $comment['post_body'];
      $postedTo = $comment['posted_to'];
      $postedBy = $comment['posted_by'];
      $dateAdded = $comment['date_added'];
      $removed = $comment['removed'];

      $dateTimeNow = date('y-m-d h:i:s');
      $startDate = new DateTime($dateAdded);
      $endDate = new DateTime($dateTimeNow);
      $interval = $startDate->diff($endDate);

      // 게시물 작성된지 1년 이상인가?
      if ($interval->y >= 1) {
        $timeMessage = $interval->y . "년 전";
      }
      // 게시물이 작성된지 1개월 이상인가?
      elseif ($interval->m >= 1) {
        if ($interval->d == 0) {
          $days = " 전";
        } else {
          $days = $interval->d . "일 전";
        }

        if ($interval->m >= 1) {
          $timeMessage = $interval->m . " 개월 " . $days;
        }
      }

      // 게시물이 작성된지 하루 이상인가?
      elseif ($interval->d >= 1) {
        if ($interval->d == 1) {
          $timeMessage = "어제";
        } else {
          $timeMessage = $interval->d . "일 전";
        }
      }

      // 게시물이 작성된지 1시간 이상인가?
      elseif ($interval->h >= 1) {
        if ($interval->h >= 1) {
          $timeMessage = $interval->h . "시간 전";
        }
      }

      // 게시물이 작성된지 1분 이상인가?
      elseif ($interval->i >= 1) {
        if ($interval->i >= 1) {
          $timeMessage = $interval->i . "분 전";
        }
      }

      // 게시물이 작성된지 30초 미만인가?
      else {
        if ($interval->s < 30) {
          $timeMessage = "방금";
        } else {
          $timeMessage = $interval->s . "초 전";
        }
      }

      $userObj = new User($con, $postedBy);
  ?>

      <div class="comment_section">
        <a href="<?= $postedBy; ?>" target="_parent"><img src="<?= $userObj->getProfilePic(); ?>" title="<?= $postedBy; ?>" style="float:left" height="30" alt="프로필 사진"></a>
        <a href="<?= $postedBy; ?>" target="_parent"><b><?= $userObj->getFirstAndLastName(); ?></b></a>
        &nbsp;&nbsp;&nbsp;&nbsp; <?= $timeMessage . "<br>" . $commentBody; ?>
        <hr>
      </div>

  <?php
    }
  } else {
    echo "<center><br><br>보여줄 댓글이 없습니다!</center>";
  }
  ?>

  <script src="assets/js/commentSectionToggle.js"></script>
</body>

</html>
