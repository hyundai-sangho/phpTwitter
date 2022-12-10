<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/form_handlers/settings_handler.php';

?>

<div class="main_column column">
  <h4>계정 설정</h4>
  <?php
  echo "<img src='" . $user['profile_pic'] . "' class='small_profile_pic'>";
  ?>
  <br>
  <a href='upload.php'>새 프로필 사진 업로드</a><br><br><br>

  <p>값을 수정하고 '세부 정보 업데이트'를 클릭하십시오.</p>

  <?php
  $userDataQuery = mysqli_query($con, "SELECT first_name, last_name, email FROM users WHERE username='$userLoggedIn'");
  $row = mysqli_fetch_array($userDataQuery);

  $firstName = $row['first_name'];
  $lastName = $row['last_name'];
  $email = $row['email'];
  ?>

  <form action="settings.php" method="POST">
    <span class='settings_text'>이름:</span> <input type="text" name="first_name" value="<?= $firstName; ?>" id="settings_input"><br>
    <span class='settings_text'>성:</span> <input type="text" name="last_name" value="<?= $lastName; ?>" id="settings_input"><br>
    <span class='settings_text'>이메일:</span> <input type="text" name="email" value="<?= $email; ?>" id="settings_input"><br>

    <?= $message; ?>

    <input type="submit" name="update_details" id="save_details" value="기본정보 업데이트" class="info settings_submit"><br>
  </form>

  <h4>비밀번호 변경</h4>
  <form action="settings.php" method="POST">
    <span class='settings_text'>기존 비밀번호:</span> <input type="password" name="old_password" id="settings_input"><br>
    <span class='settings_text'>새 비밀번호:</span> <input type="password" name="new_password_1" id="settings_input"><br>
    <span class='settings_text'>새 비밀번호 확인:</span> <input type="password" name="new_password_2" id="settings_input"><br>

    <?= $passwordMessage; ?>

    <input type="submit" name="update_password" id="save_details" value="비밀번호 업데이트" class="info settings_submit"><br>
  </form>

  <h4>계정 해지</h4>
  <form action="settings.php" method="POST">
    <input type="submit" name="close_account" id="close_account" value="계정 해지" class="danger settings_submit">
  </form>
</div>
