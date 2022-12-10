<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Post.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Notification.php';

if (isset($_POST['post_body'])) {
  $post = new Post($con, $_POST['user_from']);
  $post->submitPost($_POST['post_body'], $_POST['user_to'], '');
}
