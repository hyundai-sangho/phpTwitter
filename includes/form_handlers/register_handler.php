<?php

// 오류를 방지하기 위해 변수를 선언합니다
$fname = "";       // 이름
$lname = "";       // 성
$em = "";          // 이메일
$em2 = "";         // 이메일 확인
$password = "";    // 패스워드
$password2 = "";   // 패스워드 확인
$date = "";        // 가입 날짜
$error_array = array(); // 오류 메시지를 배열에 저장

// 에러 문구를 상수로 저장
define("ALREADY_EMAIL", "이미 사용하고 있는 이메일입니다.<br>");
define("WRONG_EMAIL", "잘못된 이메일 형식입니다.<br>");
define("EMAIL_DOES_NOT_MATCH", "이메일이 일치하지 않습니다.<br>");
define("FIRST_NAME_BETWEEN_2_AND_25", "이름은 2 ~ 25자 사이여야 합니다.<br>");
define("LAST_NAME_2_CHARACTERS_OR_LESS", "성은 2자 이하여야 합니다.<br>");
define("PASSWORD_DOES_NOT_MATCH", "비밀번호가 일치하지 않습니다.<br>");
define("PASSWORD_ONLY_ENGLISH_AND_NUMERIC", "비밀번호는 오직 영어와 숫자만 가능합니다.<br>");
define("PASSWORD_BETWEEN_5_AND_30", "비밀번호는 5 ~ 30자 사이여야 합니다.<br>");
define("ALL_COMPLETED", "<span style='color: #14C800;'>모든 설정이 완료됐습니다. 로그인하십시오.</span><br>");

if (isset($_POST['register_button'])) {

  // 이름
  $fname = strip_tags($_POST['reg_fname']);                       // html 태그 제거
  $fname = str_replace(' ', '', $fname);      // 공백 제거
  $fname = ucfirst(strtolower($fname));                  // 소문자로 변환 후 첫 글자만 대문자로 변환
  $_SESSION['reg_fname'] = $fname;                                         // 이름을 세션 변수에 저장합니다.

  // 성
  $lname = strip_tags($_POST['reg_lname']);                       // html 태그 제거
  $lname = str_replace(' ', '', $lname);      // 공백 제거
  $lname = ucfirst(strtolower($lname));                  // 소문자로 변환 후 첫 글자만 대문자로 변환
  $_SESSION['reg_lname'] = $lname;                                         // 성을 세션 변수에 저장합니다.

  // 이메일
  $em = strip_tags($_POST['reg_email']);                          // html 태그 제거
  $em = str_replace(' ', '', $em);            // 공백 제거
  $em = ucfirst(strtolower($em));                        // 소문자로 변환 후 첫 글자만 대문자로 변환
  $_SESSION['reg_email'] = $em;                                            // 이메일을 세션 변수에 저장합니다.


  // 이메일 확인
  $em2 = strip_tags($_POST['reg_email2']);                        // html 태그 제거
  $em2 = str_replace(' ', '', $em2);          // 공백 제거
  $em2 = ucfirst(strtolower($em2));                      // 소문자로 변환 후 첫 글자만 대문자로 변환
  $_SESSION['reg_email2'] = $em2;                                          // 이메일 확인을 세션 변수에 저장합니다.

  // 비밀번호, 비밀번호 확인
  $password = strip_tags($_POST['reg_password']);                 // html 태그 제거
  $password2 = strip_tags($_POST['reg_password2']);               // html 태그 제거

  $date = date("Y-m-d H:i:s");                                          // 현재 날짜

  if ($em == $em2) {
    // 이메일이 유효한 형식인지 검사
    // filter_var() 함수는 필터링된 데이터를 반환하거나 필터가 실패하면 False를 반환합니다.
    if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
      $em = filter_var($em, FILTER_VALIDATE_EMAIL);

      // 이메일이 이미 존재하는지 검사
      $e_check = mysqli_query($con, "SELECT email FROM users WHERE email='$em'");

      // 반환된 행 수를 계산합니다.
      $num_rows = mysqli_num_rows($e_check);

      if ($num_rows > 0) {
        array_push($error_array, ALREADY_EMAIL);
      }
    } else {
      array_push($error_array, WRONG_EMAIL);
    }
  } else {
    array_push($error_array, EMAIL_DOES_NOT_MATCH);
  }

  if (strlen($fname) > 25 || strlen($fname) <= 1) {
    array_push($error_array, FIRST_NAME_BETWEEN_2_AND_25);
  }

  if (strlen($lname) > 6) {
    array_push($error_array, LAST_NAME_2_CHARACTERS_OR_LESS);
  }

  if ($password != $password2) {
    array_push($error_array, PASSWORD_DOES_NOT_MATCH);
  } else {
    // preg_match를 이용해서 패스워드에 A-Za-z0-9를 제외한 문자가 들어가면
    // 예를 들어 한글이나 다른 특수문자들이 들어가면
    // "비밀번호는 오직 영어와 숫자만 가능합니다." 출력
    // ^은 not(반대)을 의미
    if (preg_match('/[^A-Za-z0-9]/', $password)) {
      array_push($error_array, PASSWORD_ONLY_ENGLISH_AND_NUMERIC);
    }
  }

  if (strlen($password) > 30 || strlen($password) <= 4) {
    array_push($error_array, PASSWORD_BETWEEN_5_AND_30);
  }

  if (empty($error_array)) {
    $password = md5($password); // 데이터베이스로 전송하기 전에 암호를 암호화합니다.

    // 이름과 성을 연결하여 사용자 이름을 생성합니다.
    $username = strtolower($lname . $fname);
    $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");

    $i = 0;
    // 사용자 이름이 존재하면 사용자 이름에 숫자를 추가합니다
    while (mysqli_num_rows($check_username_query) != 0) {

      $i++; // $i 값에 1 증가
      $username = $username . "_" . $i;
      $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
    }

    // 프로필 사진 할당
    $rand = rand(1, 2); // 1과 2 사이의 임의 번호

    if ($rand == 1) {
      $profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
    } elseif ($rand == 2) {
      $profile_pic = "assets/images/profile_pics/defaults/head_emerald.png";
    }

    $query = mysqli_query($con, "INSERT INTO users(first_name, last_name, username, email, password, signup_date, profile_pic, num_posts, num_likes, user_closed, friend_array) VALUES('$fname','$lname','$username','$em','$password','$date','$profile_pic','0','0','no',',')");

    array_push($error_array, ALL_COMPLETED);

    // 세션 변수 청소
    $_SESSION['reg_fname'] = '';
    $_SESSION['reg_lname'] = '';
    $_SESSION['reg_email'] = '';
    $_SESSION['reg_email2'] = '';
  }
}
