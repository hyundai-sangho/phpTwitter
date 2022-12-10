<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/config/config.php';                 // maria DB 연결
include_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/classes/User.php';         // 사용자 관련 클래스

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];
$usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");

if ($query != "") {
  while ($row = mysqli_fetch_array($usersReturnedQuery)) {
    $user = new User($con, $userLoggedIn);

    if ($row['username'] != $userLoggedIn) {
      $mutualFriends = $user->getMutualFriends($row['username']) . "명의 공통된 친구";
    } else {
      $mutualFriends = '';
    }

    echo "<div class='resultDisplay'>
            <a href='" . $row['username'] . "' style='color: #1485bd'>
              <div class='liveSearchProfilePic'>
                <img src='" . $row['profile_pic'] . "'>
              </div>

              <div class='liveSearchText'>
                <p>" . $row['username'] . "</p>
                <p id='grey'>" . $mutualFriends . "</p>
              </div>
            </a>
          </div>";
  }
}
