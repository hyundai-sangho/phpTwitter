<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/form_handlers/register_handler.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/form_handlers/login_handler.php';

?>

<!DOCTYPE html>
<html lang="ko">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>register.php</title>
  <link rel="stylesheet" href="assets/css/register_style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="assets/js/register.js"></script>
</head>

<body>

  <?php
  if (isset($_POST['register_button'])) {
    echo "
    <script>
    $(document).ready(function(){
      $('#first').hide();
      $('#second').show();
    });
    </script>";
  }
  ?>

  <div class="wrapper">
    <div class="login_box">
      <div class="login_header">
        <h1>소셜 네트워크</h1>
        로그인 & 회원 가입
      </div>

      <div id="first">
        <form action="register.php" method="post">
          <input type="email" name="log_email" placeholder="이메일 주소" value="<?php if (isset($_SESSION['log_email'])) {
                                                                              echo $_SESSION['log_email'];
                                                                            }
                                                                            ?>" required>
          <br>
          <input type="password" name="log_password" placeholder="비밀번호">
          <br>
          <?php if (in_array(EMAIL_OR_PASSWORD_INCORRECT, $error_array)) {
            echo EMAIL_OR_PASSWORD_INCORRECT;
          } ?>
          <input type="submit" name="login_button" value="로그인">
          <br>
          <a href="#" id="signUp" class="signUp">계정이 없다면 회원 가입</a>

        </form>
      </div>


      <div id="second">
        <form action="register.php" method="post">
          <input type="text" name="reg_fname" placeholder="이름" value="<?php if (isset($_SESSION['reg_fname'])) {
                                                                        echo $_SESSION['reg_fname'];
                                                                      } ?>" required>
          <br>

          <?php if (in_array(FIRST_NAME_BETWEEN_2_AND_25, $error_array)) {
            echo FIRST_NAME_BETWEEN_2_AND_25;
          } ?>

          <input type="text" name="reg_lname" placeholder="성" value="<?php if (isset($_SESSION['reg_lname'])) {
                                                                        echo $_SESSION['reg_lname'];
                                                                      } ?>" required>
          <br>

          <?php if (in_array(LAST_NAME_2_CHARACTERS_OR_LESS, $error_array)) {
            echo LAST_NAME_2_CHARACTERS_OR_LESS;
          } ?>

          <input type="email" name="reg_email" placeholder="이메일" value="<?php if (isset($_SESSION['reg_email'])) {
                                                                          echo $_SESSION['reg_email'];
                                                                        } ?>" required>
          <br>
          <input type="email" name="reg_email2" placeholder="이메일 확인" value="<?php if (isset($_SESSION['reg_email2'])) {
                                                                              echo $_SESSION['reg_email2'];
                                                                            } ?>" required>
          <br>

          <?php if (in_array(ALREADY_EMAIL, $error_array)) {
            echo ALREADY_EMAIL;
          } elseif (in_array(WRONG_EMAIL, $error_array)) {
            echo WRONG_EMAIL;
          } elseif (in_array(EMAIL_DOES_NOT_MATCH, $error_array)) {
            echo EMAIL_DOES_NOT_MATCH;
          } ?>

          <input type="password" name="reg_password" placeholder="비밀번호" required>
          <br>
          <input type="password" name="reg_password2" placeholder="비밀번호 확인" required>
          <br>

          <?php if (in_array(PASSWORD_DOES_NOT_MATCH, $error_array)) {
            echo PASSWORD_DOES_NOT_MATCH;
          } elseif (in_array(PASSWORD_ONLY_ENGLISH_AND_NUMERIC, $error_array)) {
            echo PASSWORD_ONLY_ENGLISH_AND_NUMERIC;
          } elseif (in_array(PASSWORD_BETWEEN_5_AND_30, $error_array)) {
            echo PASSWORD_BETWEEN_5_AND_30;
          } ?>

          <input type="submit" name="register_button" value="등록">
          <br>

          <?php if (in_array(ALL_COMPLETED, $error_array)) {
            echo ALL_COMPLETED;
          } ?>
          <a href="#" id="signIn" class="signIn">계정이 있다면 로그인</a>
        </form>
      </div>

    </div>
  </div>
</body>

</html>
