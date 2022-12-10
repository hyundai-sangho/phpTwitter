<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/header.php';

if (isset($_POST['cancel'])) {
  header('Location: settings.php');
}

if (isset($_POST['close_account'])) {
  $closeQuery = mysqli_query($con, "UPDATE users SET user_closed='yes' WHERE username='$userLoggedIn'");
  session_destroy();
  header('Location: register.php');
}

?>

<div class="main_column column">
  <h4>계정 해지</h4>

  <pre>
<span style="color: red;">계정을 해지하시겠습니까?</span>
계정을 닫으면 프로필과 모든 활동이 다른 사용자에게 숨겨집니다.
로그인만 하면 언제든지 계정을 다시 열 수 있습니다.
</pre>

  <form action="close_account.php" method="POST">
    <input type="submit" name="close_account" id="close_account" value="YES" class="danger settings_submit">
    <input type="submit" name="cancel" id="update_details" value="NO" class="info settings_submit">
  </form>
</div>
