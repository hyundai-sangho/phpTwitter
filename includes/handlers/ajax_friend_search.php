<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/User.php';

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

if ($query != "") {
  $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");

  while ($row = mysqli_fetch_array($usersReturned)) {
    $user = new User($con, $userLoggedIn);

    if ($row['username'] != $userLoggedIn) {
      $mutualFriends = "공통의 친구 " . $user->getMutualFriends($row['username']) . "명";
    } else {
      $mutualFriends = "나";
    }

    if ($user->isFriend($row['username'])) {
      echo "<div class='resultDisplay'>
              <a href='messages.php?u=" . $row['username'] . "' style='color: #000'>
                <div class='liveSearchProfilePic'>
                  <img src='" . $row['profile_pic'] . "'>
                </div>

                <div class='liveSearchText'>
                  <p>" . $row['username'] . "</p>
                  <p id='grey' style='margin: 0'>" . $mutualFriends . "</p>
                </div>
              </a>
            </div>";
    }
  }
}
