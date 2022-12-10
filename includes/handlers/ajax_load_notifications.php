<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Notification.php';

$limit = 6; //로드할 메시지 수

$notification = new Notification($con, $_REQUEST['userLoggedIn']);
echo $notification->getNotifications($_REQUEST, $limit);
