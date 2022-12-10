<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/User.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/Message.php';

$limit = 6; //로드할 메시지 수

$message = new Message($con, $_REQUEST['userLoggedIn']);
echo $message->getConvosDropdown($_REQUEST, $limit);
