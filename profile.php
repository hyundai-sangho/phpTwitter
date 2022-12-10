<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/header.php';

$messageObj = new Message($con, $userLoggedIn);

if (isset($_GET['profile_username'])) {
  $username = $_GET['profile_username'];
  $userDetailsQuery = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
  $userArray = mysqli_fetch_array($userDetailsQuery);
  $numFriends = (substr_count($userArray['friend_array'], ",")) - 1;
}

if (isset($_POST['remove_friend'])) {
  $user = new User($con, $userLoggedIn);
  $user->removeFriend($username);
}

if (isset($_POST['add_friend'])) {
  $user = new User($con, $userLoggedIn);
  $user->sendRequest($username);
}

if (isset($_POST['respond_request'])) {
  header("Location: requests.php");
}

if (isset($_POST['post_message']) && isset($_POST['message_body'])) {
  $body = mysqli_real_escape_string($con, $_POST['message_body']);
  $date = date('y-m-d h:i:s');
  $messageObj->sendMessage($username, $body, $date);

  $link = '#profileTabs a[href="#messages_div"]';
  echo "<script>
          $(function(){
            $('" . $link . "').tab('show');
          });
        </script>";
}

?>

<style>
  .wrapper {
    margin-left: 0px;
    padding-left: 0px;
  }
</style>

<div class="profile_left" data-user-logged-in="<?= $userLoggedIn; ?>" data-profile-username="<?= $username; ?>" id="profile">
  <img src="<?= $userArray['profile_pic']; ?>" alt="프로필 사진">

  <div class="profile_info">
    <p><?= "게시물: " . $userArray['num_posts']; ?></p>
    <p><?= "좋아요: " . $userArray['num_likes']; ?></p>
    <p><?= "친구: " . $numFriends; ?></p>
  </div>

  <form action="<?= $username; ?>" method="POST">
    <?php
    $profileUserObj = new User($con, $username);

    if ($profileUserObj->isClosed()) {
      header("Location: user_closed.php");
    }

    $loggedInUserObj = new User($con, $userLoggedIn);

    if ($userLoggedIn != $username) {
      if ($loggedInUserObj->isFriend($username)) {
        echo '<input type="submit" name="remove_friend" class="danger" value="친구 취소">';
      } elseif ($loggedInUserObj->didReceiveRequest($username)) {
        echo '<input type="submit" name="respond_request" class="warning" value="요청에 응답하십시오.">';
      } elseif ($loggedInUserObj->didSendRequest($username)) {
        echo '<input type="submit" name="" class="default" value="보낸 요청">';
      } else {
        echo '<input type="submit" name="add_friend" class="success" value="친구 추가">';
      }
    }
    ?>
  </form>
  <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post something">

  <?php
  if ($userLoggedIn != $username) {
    echo '<div class="profile_info_bottom">';
    echo "상호 친구 " .  $loggedInUserObj->getMutualFriends($username) . "명";
    echo '</div>';
  }
  ?>
</div>

<div class="profile_main_column column">

  <ul class="nav nav-tabs" role="tablist" id="profileTabs">
    <li role="presentation" class="active"><a href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">Newsfeed</a></li>
    <li role="presentation"><a href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">Messages</a></li>
  </ul>

  <div class="tab-content">
    <div role="tabpanel" class="tab-pane fade in active" id="newsfeed_div">
      <div class="posts_area"></div>
      <img src="assets/images/icons/loading.gif" alt="로딩 이미지" id="loading">
    </div>


    <div role="tabpanel" class="tab-pane fade" id="messages_div">
      <?php
      echo "<h4>당신과 <a href='" . $username . "'>" . $profileUserObj->getFirstAndLastName() . "의 대화</a></h4><hr><br>";
      echo "<div class='loaded_messages' id='scroll_messages'>";
      echo $messageObj->getMessages($username);
      echo "</div>";
      ?>

      <div class="message_post">
        <form action="" method="POST">
          <textarea name='message_body' id='message_textarea' placeholder='당신의 메시지를 작성'></textarea>
          <input type='submit' name='post_message' class='info' id='message_submit' value='보내기'>
        </form>
      </div>

      <script>
        let div = document.getElementById('scroll_messages');
        div.scrollTop = div.scrollHeight;
      </script>
    </div>
  </div>
</div>

<!-- 모달 -->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="tre">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Post something!!</h4>
      </div>

      <div class="modal-body">
        <p>이것은 사용자의 프로필 페이지와 친구들이 볼 수 있는 뉴스 피드에 나타납니다.</p>

        <form class="profile_post" method="POST">
          <div class="form-group">
            <textarea class="form-control" name="post_body"></textarea>
            <input type="hidden" name="user_from" value="<?= $userLoggedIn; ?>">
            <input type="hidden" name="user_to" value="<?= $username; ?>">
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
      </div>

    </div>
  </div>
</div>

</div>

<script src="assets/js/ajaxLoadProfile.js"></script>

</body>

</html>
