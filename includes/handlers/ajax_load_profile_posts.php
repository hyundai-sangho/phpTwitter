<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Post.php';

$limit = 10;

$posts = new Post($con, $_REQUEST['userLoggedIn']);
$posts->loadProfilePosts($_REQUEST, $limit);
