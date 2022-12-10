<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/header.php';

if (isset($_GET['q'])) {
  $query = $_GET['q'];
} else {
  $query = "";
}

if (isset($_GET['type'])) {
  $type = $_GET['type'];
} else {
  $type = "name";
}

?>

<div class="main_column column" id="main_column">
  <?php
  if ($query == "") {
    echo "검색 상자에 무언가를 입력해야 합니다.";
  } else {
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");

    // 결과가 발견되었는지 확인
    if (mysqli_num_rows($usersReturnedQuery) == 0) {
      echo $query . "와 같은 이름의 사용자를 찾을 수 없습니다.";
    } else {
      echo mysqli_num_rows($usersReturnedQuery) . "개의 결과 발견: <br><br>";
    }
  }

  echo "<p id='grey'>다음을 검색해 보세요.</p>";
  echo "<a href='search.php?q=" . $query . "&type=name'>Names</a>, <a href='search.php?q=" . $query . "&type=username'>Usernames</a><hr id='search_hr'>";

  while ($row = mysqli_fetch_array($usersReturnedQuery)) {
    $userObj = new User($con, $user['username']);

    $button = "";
    $mutualFriends = "";

    if ($user['username'] != $row['username']) {
      // 우정 상태에 따라 버튼 생성
      if ($userObj->isFriend($row['username'])) {
        $button = "<input type='submit' name='" . $row['username'] . "' class='danger' value='친구 삭제'>";
      } elseif ($userObj->didReceiveRequest($row['username'])) {
        $button = "<input type='submit' name='" . $row['username'] . "' class='warning' value='요청에 응답'>";
      } elseif ($userObj->didSendRequest($row['username'])) {
        $button = "<input type='submit' class='default' value='요청 전송됨'>";
      } else {
        $button = "<input type='submit' name='" . $row['username'] . "' class='success' value='친구 추가'>";
      }

      $mutualFriends = $userObj->getMutualFriends(($row['username'])) . "명의 공통의 친구";

      // 버튼 양식
      if (isset($_POST[$row['username']])) {
        if ($userObj->isFriend($row['username'])) {
          $userObj->removeFriend($row['username']);
          header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        } elseif ($userObj->didReceiveRequest($row['username'])) {
          header('Location: requests.php');
        } elseif ($userObj->didSendRequest($row['username'])) {
        } else {
          $userObj->sendRequest($row['username']);
          header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        }
      }
    }

    echo "<div class='search_result'>
            <div class='searchPageFriendButtons'>
              <form action='' method='POST'>
                " . $button . "
              </form>
            </div>

            <div class='result_profile_pic'>
              <a href='" . $row['username'] . "'><img src='" . $row['profile_pic'] . "' style='height: 100px;'></a>
            </div>
              <a href='" . $row['username'] . "'>" . $row['username'] . "
              </a>
              <br><br><br><br>
              " . $mutualFriends . "<br>
          </div>
          <hr id='search_hr'>";
  }

  ?>
</div>
