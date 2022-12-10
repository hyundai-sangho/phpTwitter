<?php

if (isset($_POST['update_details'])) {
  $firstName = $_POST['first_name'];
  $lastName = $_POST['last_name'];
  $email = $_POST['email'];

  $emailCheck = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");
  $row = mysqli_fetch_array($emailCheck);
  $matchedUser = $row['username'];

  if ($matchedUser == "" || $matchedUser == $userLoggedIn) {
    $message = "기본정보 업데이트!!<br><br>";

    $query = mysqli_query($con, "UPDATE users SET first_name='$firstName', last_name='$lastName', email='$email' WHERE username='$userLoggedIn'");
  } else {
    $message = "해당 이메일은 이미 사용 중입니다.!!<br><br>";
  }
} else {
  $message = "";
}

//**********************************
if (isset($_POST['update_password'])) {
  $oldPassword = strip_tags($_POST['old_password']);
  $newPassword1 = strip_tags($_POST['new_password_1']);
  $newPassword2 = strip_tags($_POST['new_password_2']);

  $passwordQuery = mysqli_query($con, "SELECT password FROM users WHERE username='$userLoggedIn'");
  $row = mysqli_fetch_array($passwordQuery);
  $dbPassword = $row['password'];

  if (md5($oldPassword) == $dbPassword) {
    if ($newPassword1 == $newPassword2) {

      if (strlen($newPassword1) <= 4) {
        $passwordMessage = "죄송합니다, 비밀번호는 4자 이상이어야 합니다.<br><br>";
      } else {
        $newPasswordMd5 = md5($newPassword1);
        $passwordQuery = mysqli_query($con, "UPDATE users SET password='$newPasswordMd5' WHERE username='$userLoggedIn'");
        $passwordMessage = "비밀번호가 변경되었습니다!!<br><br>";
      }
    } else {
      $passwordMessage = "두 비밀번호가 일치해야 합니다!!<br><br>";
    }
  } else {
    $passwordMessage = "이전 비밀번호가 잘못되었습니다!!<br><br>";
  }
} else {
  $passwordMessage = "";
}

//**********************************
if (isset($_POST['close_account'])) {
  header("Location: close_account.php");
}
