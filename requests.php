<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/header.php';

?>

<div class="main_column column" id="main_column">
  <h4>친구 요청</h4>

  <?php

  $query = mysqli_query($con, "SELECT * FROM friend_requests WHERE user_to='$userLoggedIn'");
  if (mysqli_num_rows($query) == 0) {
    echo "현재 친구 요청이 없습니다";
  } else {
    while ($row = mysqli_fetch_array($query)) {
      $userFrom = $row['user_from'];
      $userFromObj = new User($con, $userFrom);

      echo $userFromObj->getFirstAndLastName() . "가 친구 요청을 보냈습니다";

      $userFromFriendArray = $userFromObj->getFriendArray();

      if (isset($_POST['accept_request' . $userFrom])) {
        $addFriendQuery = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$userFrom,') WHERE username='$userLoggedIn'");
        $addFriendQuery = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username='$userFrom'");

        $deleteQuery = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$userFrom'");
        echo "당신은 이제 친구입니다.";
        header("Location: requests.php");
      }

      if (isset($_POST['ignore_request' . $userFrom])) {
        $deleteQuery = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$userFrom'");
        echo "요청이 무시됩니다.";
        header("Location: requests.php");
      }

  ?>

      <form action="requests.php" method="POST">
        <input type="submit" name="accept_request<?= $userFrom; ?>" id="accept_button" value="수락">
        <input type="submit" name="ignore_request<?= $userFrom; ?>" id="ignore_button" value="거절">
      </form>

  <?php
    }
  }
  ?>


</div>
