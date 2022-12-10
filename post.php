<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/header.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
} else {
  $id = 0;
}

?>

<div class="user_details column">
  <a id="userName" href="<?= $userLoggedIn; ?>" data-userName="<?= $userLoggedIn; ?>"><img src="<?= $user['profile_pic']; ?>" alt="프로필 사진"></a>

  <div class="user_details_left_right">
    <a href="<?= $userLoggedIn; ?>"><?= $user['username']; ?></a>
    <br>
    <span><?= "게시물: " . $user['num_posts']; ?></span>
    <br>
    <span><?= "좋아요: " . $user['num_likes']; ?></span>
  </div>
</div>

<div class="main_column column" id="main_column">
  <div class="posts_area">
    <?php
      $post = new Post($con, $userLoggedIn);
      $post->getSinglePost($id);
    ?>
  </div>
</div>
