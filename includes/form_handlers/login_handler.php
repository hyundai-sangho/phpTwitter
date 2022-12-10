<?php

define('EMAIL_OR_PASSWORD_INCORRECT', '이메일 혹은 패스워드가 일치하지 않습니다.<br>');

if (isset($_POST['login_button'])) {
  $email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL); // 이메일 데이터 살균

  $_SESSION['log_email'] = $email; // 세션 변수에 이메일 데이터 저장
  $password = md5($_POST['log_password']);

  $checkDatabaseQuery = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND password='$password'");
  $checkLoginQuery = mysqli_num_rows($checkDatabaseQuery);

  if ($checkLoginQuery == 1) {
    $row = mysqli_fetch_array($checkDatabaseQuery);
    $username = $row['username'];

    $userClosedQuery = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND user_closed = 'yes';");
    if (mysqli_num_rows($userClosedQuery) == 1) {
      $reopenAccount = mysqli_query($con, "UPDATE users SET user_closed='no' WHERE email='$email';");
    }

    $_SESSION['username'] = $username;
    header('Location: index.php');
    exit();
  } else {
    array_push($error_array, EMAIL_OR_PASSWORD_INCORRECT);
  }
}
