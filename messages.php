<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/header.php';

$messageObj = new Message($con, $userLoggedIn);

if (isset($_GET['u'])) {
  $userTo = $_GET['u'];
} else {
  $userTo = $messageObj->getMostRecentUser();

  // $userTo가 false면
  if (!$userTo) {
    $userTo = 'new';
  }
}

if ($userTo != 'new') {
  $userToObj = new User($con, $userTo);
}

if (isset($_POST['post_message']) && isset($_POST['message_body'])) {
  $body = mysqli_real_escape_string($con, $_POST['message_body']);
  $date = date('y-m-d h:i:s');
  $messageObj->sendMessage($userTo, $body, $date);
}

?>

<div class="user_details column">
  <a href="<?= $userLoggedIn; ?>"><img src="<?= $user['profile_pic']; ?>" alt="프로필 사진"></a>

  <div class="user_details_left_right">
    <a href="<?= $userLoggedIn; ?>"><?= $user['username']; ?></a>
    <br>
    <span><?= "게시물: " . $user['num_posts']; ?></span>
    <br>
    <span><?= "좋아요: " . $user['num_likes']; ?></span>

  </div>
</div>

<div class="main_column column" id="main_column">
  <?php
  if ($userTo != "new") {
    echo "<h4>당신과 <a href='$userTo'>" . $userToObj->getFirstAndLastName() . "의 대화</a></h4><hr><br>";
    echo "<div class='loaded_messages' id='scroll_messages'>";
    echo $messageObj->getMessages($userTo);
    echo "</div>";
  } else {
    echo "<h4>새로운 메시지</h4>";
  }
  ?>

  <div class="message_post">
    <form action="" method="POST">
      <?php
      if ($userTo == 'new') {
        echo "메시지를 보낼 친구를 선택하세요. <br><br>";
      ?>
        To: <input type='text' onkeyup='getUsers(this.value, "<?= $userLoggedIn; ?>")' name='q' placeholder='이름' autocomplete='off' id='search_text_input'>
      <?php
        echo "<div class='results'></div>";
      } else {
        echo "<textarea name='message_body' id='message_textarea' placeholder='당신의 메시지를 작성'></textarea>";
        echo "<input type='submit' name='post_message' class='info' id='message_submit' value='보내기'>";
      }
      ?>
    </form>
  </div>

  <script>
    let div = document.getElementById('scroll_messages');
    div.scrollTop = div.scrollHeight;
  </script>
</div>

<div class="user_details column" id="conversations">
  <h4>대화 상대</h4>

  <div class="loaded_conversations">
    <?= $messageObj->getConvos(); ?>
  </div>
  <br>
  <a href="messages.php?u=new">새로운 메시지</a>
</div>
