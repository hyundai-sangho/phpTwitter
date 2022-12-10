<?php

// https://m.blog.naver.com/PostView.naver?isHttpsRedirect=true&blogId=bluesunh&logNo=39807140
// ob_start(); // 출력 버퍼링을 켭니다.
session_start();

$timezone = date_default_timezone_set("Asia/Seoul");

// phpdotenv 사용 ============================================
// phpdotenv 사용시 .env 파일은 같은 폴더 안에 있어야 아래 코드가 제대로 동작함.

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$HOSTNAME = $_ENV['HOSTNAME'];
$USERNAME = $_ENV['USERNAME'];
$PASSWORD = $_ENV['PASSWORD'];
$DATABASE = $_ENV['DATABASE'];

$con = mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);
// phpdotenv 사용 ============================================

if (mysqli_connect_errno()) {
  echo "연결 실패 " . mysqli_connect_errno();
}
