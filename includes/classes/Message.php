<?php

/**
 * 메시지 관련 다양한 함수를 포함한 클래스
 * 1. getMostRecentUser() 메시지를 누구한테 보낼건지
 * 2. sendMessage() 메시지 보내기
 * 3. getMessages() 메시지 가져오기
 * 4. getLatestMessage() 메시지 보낸 시간 및 누가 보낸건지
 * 5. getConvos() 대화 상대 표시
 * 6. getConvosDropdown() 드롭다운 메시지
 * 7. getUnreadNumber() 읽지 않은 메시지
 */
class Message
{
  private $userObj;
  private $con;

  public function __construct($con, $user)
  {
    $this->con = $con;
    $this->userObj = new User($con, $user);
  }

  public function getMostRecentUser()
  {
    $userLoggedIn = $this->userObj->getUsername();

    $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from='$userLoggedIn' ORDER BY id DESC LIMIT 1");

    if (mysqli_num_rows($query) == 0) {
      return false;
    }

    $row = mysqli_fetch_array($query);
    $userTo = $row['user_to'];
    $userFrom = $row['user_from'];

    if ($userTo != $userLoggedIn) {
      return $userTo;
    } else {
      return $userFrom;
    }
  }

  public function sendMessage($userTo, $body, $date)
  {
    if ($body != "") {
      $userLoggedIn = $this->userObj->getUsername();
      $query = mysqli_query($this->con, "INSERT INTO messages(user_to, user_from, body, date, opened, viewed, deleted) VALUES('$userTo', '$userLoggedIn', '$body', '$date', 'no', 'no', 'no')");
    }
  }

  public function getMessages($otherUser)
  {
    $userLoggedIn = $this->userObj->getUsername();
    $data = '';

    $query = mysqli_query($this->con, "UPDATE messages SET opened='yes' WHERE user_to='$userLoggedIn' AND user_from='$otherUser'");
    $getMessagesQuery = mysqli_query($this->con, "SELECT * FROM messages WHERE (user_to='$userLoggedIn' AND user_from='$otherUser') OR (user_from ='$userLoggedIn' AND user_to='$otherUser')");

    while ($row = mysqli_fetch_array($getMessagesQuery)) {
      $userTo = $row['user_to'];
      $userFrom = $row['user_from'];
      $body = $row['body'];
      $divTop = ($userTo == $userLoggedIn) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";
      $data = $data . $divTop . $body . "</div><br><br>";
    }
    return $data;
  }

  public function getLatestMessage($userLoggedIn, $user2)
  {
    $detailsArray = array();
    $query = mysqli_query($this->con, "SELECT body, user_to, date FROM messages WHERE (user_to='$userLoggedIn' AND user_from='$user2') OR (user_to='$user2' AND user_from='$userLoggedIn') ORDER BY id DESC LIMIT 1");

    $row = mysqli_fetch_array($query);
    $sentBy = ($row['user_to'] == $userLoggedIn) ? "그 말: " : "내 말: ";

    // 기간
    $dateTimeNow = date('y-m-d h:i:s');
    $startDate = new DateTime($row['date']);
    $endDate = new DateTime($dateTimeNow);
    $interval = $startDate->diff($endDate);

    // 게시물 작성된지 1년 이상인가?
    if ($interval->y >= 1) {
      $timeMessage = $interval->y . "년 전";
    }
    // 게시물이 작성된지 1개월 이상인가?
    elseif ($interval->m >= 1) {
      if ($interval->d == 0) {
        $days = " 전";
      } else {
        $days = $interval->d . "일 전";
      }

      if ($interval->m >= 1) {
        $timeMessage = $interval->m . " 개월 " . $days;
      }
    }

    // 게시물이 작성된지 하루 이상인가?
    elseif ($interval->d >= 1) {
      if ($interval->d == 1) {
        $timeMessage = "어제";
      } else {
        $timeMessage = $interval->d . "일 전";
      }
    }

    // 게시물이 작성된지 1시간 이상인가?
    elseif ($interval->h >= 1) {
      if ($interval->h >= 1) {
        $timeMessage = $interval->h . "시간 전";
      }
    }

    // 게시물이 작성된지 1분 이상인가?
    elseif ($interval->i >= 1) {
      if ($interval->i >= 1) {
        $timeMessage = $interval->i . "분 전";
      }
    }

    // 게시물이 작성된지 30초 미만인가?
    else {
      if ($interval->s < 30) {
        $timeMessage = "방금";
      } else {
        $timeMessage = $interval->s . "초 전";
      }
    }

    array_push($detailsArray, $sentBy);
    array_push($detailsArray, $row['body']);
    array_push($detailsArray, $timeMessage);

    return $detailsArray;
  }

  public function getConvos()
  {
    $userLoggedIn = $this->userObj->getUsername();
    $returnString = "";
    $convos = array();

    $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from='$userLoggedIn' ORDER BY id DESC");

    while ($row = mysqli_fetch_array($query)) {
      $userToPush = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from'];

      if (!in_array($userToPush, $convos)) {
        array_push($convos, $userToPush);
      }
    }

    foreach ($convos as $username) {
      $userFoundObj = new User($this->con, $username);
      $latestMessageDetails = $this->getLatestMessage($userLoggedIn, $username);

      $dots = (strlen($latestMessageDetails[1]) >= 6) ? "..." : "";
      $split = mb_str_split($latestMessageDetails[1], 6);
      $split = $split[0] . $dots;

      $returnString .=
        "<a href='messages.php?u=$username'>
          <div class='user_found_messages'>
            <img src='" . $userFoundObj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>" . $userFoundObj->getFirstAndLastName() . "
            <span class='timestamp_smaller' id='grey'>" . $latestMessageDetails[2] . "</span>
            <p id='grey' style='margin: 0;'>" . $latestMessageDetails[0] . $split . " </p>
          </div>
        </a>";
    }

    return $returnString;
  }

  public function getConvosDropdown($data, $limit)
  {
    $page = $data['page'];
    $userLoggedIn = $this->userObj->getUsername();
    $returnString = "";
    $convos = array();

    if ($page == 1) {
      $start = 0;
    } else {
      $start = ($page - 1) * $limit;
    }

    $setViewedQuery = mysqli_query($this->con, "UPDATE messages SET viewed='yes' WHERE user_to='$userLoggedIn'");

    $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from='$userLoggedIn' ORDER BY id DESC");

    while ($row = mysqli_fetch_array($query)) {
      $userToPush = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from'];

      if (!in_array($userToPush, $convos)) {
        array_push($convos, $userToPush);
      }
    }

    $numIterations = 0; // 확인된 메시지 수
    $count = 1;         // 게시된 메시지 수

    foreach ($convos as $username) {

      if ($numIterations++ < $start) {
        continue;
      }

      if ($count > $limit) {
        break;
      } else {
        $count++;
      }

      $isUnreadQuery = mysqli_query($this->con, "SELECT opened FROM messages WHERE user_to='$userLoggedIn' AND user_from='$username' ORDER BY id DESC");
      $row = mysqli_fetch_array($isUnreadQuery);
      $style = (isset($row['opened']) && $row['opened'] == 'no') ? "background-color: #ddedff;" : "";

      $userFoundObj = new User($this->con, $username);
      $latestMessageDetails = $this->getLatestMessage($userLoggedIn, $username);

      $dots = (strlen($latestMessageDetails[1]) >= 6) ? "..." : "";
      $split = mb_str_split($latestMessageDetails[1], 6);
      $split = $split[0] . $dots;

      $returnString .=
        "<a href='messages.php?u=$username'>
          <div class='user_found_messages' style='" . $style . "'>
            <img src='" . $userFoundObj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>" . $userFoundObj->getFirstAndLastName() . "
            <span class='timestamp_smaller' id='grey'>" . $latestMessageDetails[2] . "</span>
            <p id='grey' style='margin: 0;'>" . $latestMessageDetails[0] . $split . " </p>
          </div>
        </a>";
    }

    // 게시물이 로드된 경우
    if ($count > $limit) {
      $returnString .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropdownData' value='false'>";
    } else {
      $returnString .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align: center;'>더 이상 로드할 메시지가 없습니다!!</p>";
    }

    return $returnString;
  }

  /**
   * 읽지 않은 메시지 갯수를 알려주는 함수
   */
  public function getUnreadNumber()
  {
    $userLoggedIn = $this->userObj->getUsername();
    $query = mysqli_query($this->con, "SELECT * FROM messages WHERE viewed='no' AND user_to='$userLoggedIn'");

    return mysqli_num_rows($query);
  }
}
