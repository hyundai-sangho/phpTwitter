<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Post.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Message.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Notification.php';

if (isset($_SESSION['username'])) {
  $userLoggedIn = $_SESSION['username'];
  $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
  $user = mysqli_fetch_array($user_details_query);
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
  <title>소셜 네트워크</title>

  <!-- JS -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="/sns/assets/js/bootstrap.js"></script>
  <script src="/sns/assets/js/bootbox.min.js"></script>
  <script src="/sns/assets/js/demo.js"></script>
  <script src="/sns/assets/js/jquery.jcrop.js" defer></script>
  <script src="/sns/assets/js/jcrop_bits.js" defer></script>
  <script src="/sns/assets/js/ajaxLoadMessages.js" defer></script>

  <!-- CSS -->
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="/sns/assets/css/bootstrap.css">
  <link rel="stylesheet" href="/sns/assets/css/style.css">
  <link rel="stylesheet" href="/sns/assets/css/jquery.Jcrop.css">

</head>

<body>

  <div class="top_bar">
    <div class="logo">
      <a href="index.php">소셜 네트워크</a>
    </div>

    <div class="search">
      <form action="search.php" method="GET" name="search_form">
        <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?= $userLoggedIn; ?>')" name="q" placeholder="검색" autocomplete="off" id="search_text_input">

        <div class="button_holder">
          <img src="assets/images/icons/magnifying_glass.png" alt="검색 아이콘">
        </div>
      </form>

      <div class="search_results"></div>
      <div class="search_results_footer_empty"></div>
    </div>

    <nav>
      <?php
      // 읽지 않은 메시지
      $messages = new Message($con, $userLoggedIn);
      $numMessages = $messages->getUnreadNumber();

      // 읽지 않은 알림
      $notifications = new Notification($con, $userLoggedIn);
      $numNotifications = $notifications->getUnreadNumber();

      // 친구 요청 받은 횟수
      $userObj = new User($con, $userLoggedIn);
      $numRequests = $userObj->getNumberOfFriendRequests();
      ?>

      <a href="<?= $userLoggedIn; ?>">
        <?= $userLoggedIn ?>
      </a>
      <a href="index.php">
        <i class="fa fa-home fa-lg"></i>
      </a>
      <!-- javascript:void(0); 링크 기능 무효화 -->
      <a href="javascript:void(0);" onclick="getDropdownData('<?= $userLoggedIn; ?>', 'message')">
        <i class="fa fa-envelope fa-lg"></i>
        <?php
        if ($numMessages > 0) {
          echo '<span class="notification_badge" id="unread_message">' . $numMessages . '</span>';
        }
        ?>
      </a>
      <a href="javascript:void(0);" onclick="getDropdownData('<?= $userLoggedIn; ?>', 'notification')">
        <i class="fa fa-bell-o fa-lg"></i>
        <?php
        if ($numNotifications > 0) {
          echo '<span class="notification_badge" id="unread_notification">' . $numNotifications . '</span>';
        }
        ?>
      </a>
      <a href="requests.php">
        <i class="fa fa-users fa-lg"></i>
        <?php
        if ($numRequests > 0) {
          echo '<span class="notification_badge" id="unread_requests">' . $numRequests . '</span>';
        }
        ?>
      </a>
      <a href="settings.php">
        <i class="fa fa-cog fa-lg"></i>
      </a>
      <a href="includes/handlers/logout.php">
        <i class="fa fa-sign-out fa-lg"></i>
      </a>
    </nav>

    <div id="dropdownDataWindow" class="dropdown_data_window" style="height:0px; border:none;" data-userLoggedIn="<?= $userLoggedIn; ?>"></div>
    <input type="hidden" id="dropdown_data_type" value="">
  </div>

  <div class="wrapper">
