<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Post.php';

// 화면에 표시될 게시물 갯수
$limit = 10;

$posts = new Post($con, $_REQUEST['userLoggedIn']);
$posts->loadPostsFriends($_REQUEST, $limit);
