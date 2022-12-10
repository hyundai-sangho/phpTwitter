<?php

/**
 * 메시지 알림 클래스
 * 1. getUnreadNumber()
 * 2. insertNotification()
 * 3. getNotifications()
 */
class Notification
{
  private $userObj;
  private $con;

  public function __construct($con, $user)
  {
    $this->con = $con;
    $this->userObj = new User($con, $user);
  }

  /**
   * 읽지 않은 알림 갯수를 알려주는 함수
   */
  public function getUnreadNumber()
  {
    $userLoggedIn = $this->userObj->getUsername();
    $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE viewed='no' AND user_to='$userLoggedIn'");

    return mysqli_num_rows($query);
  }

  public function getNotifications($data, $limit)
  {
    $page = $data['page'];
    $userLoggedIn = $this->userObj->getUsername();
    $returnString = "";

    if ($page == 1) {
      $start = 0;
    } else {
      $start = ($page - 1) * $limit;
    }

    $setViewedQuery = mysqli_query($this->con, "UPDATE notifications SET viewed='yes' WHERE user_to='$userLoggedIn'");
    $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to='$userLoggedIn' ORDER BY id DESC");

    if (mysqli_num_rows($query) == 0) {
      echo "알림이 없습니다.";
      return;
    }

    $numIterations = 0; // 확인된 메시지 수
    $count = 1;         // 게시된 메시지 수

    while ($row = mysqli_fetch_array($query)) {

      if ($numIterations++ < $start) {
        continue;
      }

      if ($count > $limit) {
        break;
      } else {
        $count++;
      }

      $userFrom = $row['user_from'];
      $userDataQuery = mysqli_query($this->con, "SELECT * FROM users WHERE username='$userFrom'");
      $userData = mysqli_fetch_array($userDataQuery);


      // 기간
      $dateTimeNow = date('y-m-d h:i:s');
      $startDate = new DateTime($row['datetime']);
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


      $opened = $row['opened'];
      $style = $row['opened'] == 'no' ? "background-color: #ddedff;" : "";

      $returnString .=
        "<a href='" . $row['link'] . "'>
            <div class='resultDisplay resultDisplayNotification' style='" .$style . "'>
              <div class='notificationsProfilePic'>
                <img src='" . $userData['profile_pic'] . "'>
              </div>
              <p class='timestamp_smaller' id='grey'>" . $timeMessage . "</p>" . $row['message'] . "
            </div>
        </a>";
    }

    // 게시물이 로드된 경우
    if ($count > $limit) {
      $returnString .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropdownData' value='false'>";
    } else {
      $returnString .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align: center;'>더 이상 로드할 알림이 없습니다!!</p>";
    }

    return $returnString;
  }

  /**
   * 해당 알림을 디비에 저장
   */
  public function insertNotification($postId, $userTo, $type)
  {
    $userLoggedIn = $this->userObj->getUsername();
    $userLoggedInName = $this->userObj->getFirstAndLastName();

    $dateTime = date('y-m-d h:i:s');

    switch ($type) {
      case 'comment':
        $message = $userLoggedInName . "가 게시물에 댓글을 달았습니다.";
        break;
      case 'like':
        $message = $userLoggedInName . "가 게시물을 좋아했습니다.";
        break;
      case 'profile_post':
        $message = $userLoggedInName . "가 프로필에 게시";
        break;
      case 'comment_non_owner':
        $message = $userLoggedInName . "가 당신이 댓글을 단 게시물에 댓글을 달았습니다.";
        break;
      case 'profile_comment':
        $message = $userLoggedInName . "가 프로필 게시물에 댓글을 달았습니다.";
        break;
      default:
        echo '에러 발생';
        break;
    }

    $link = "post.php?id=" . $postId;
    $insertQuery = mysqli_query($this->con, "INSERT INTO notifications VALUES('', '$userTo', '$userLoggedIn', '$message', '$link', '$dateTime', 'no', 'no')");
  }
}
