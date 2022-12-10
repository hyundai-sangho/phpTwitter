<?php

class Post
{
  private $userObj;
  private $con;

  public function __construct($con, $user)
  {
    $this->con = $con;
    $this->userObj = new User($con, $user);
  }


  /** 게시물 입력 함수 */
  public function submitPost($body, $userTo, $imageName)
  {
    $body = strip_tags($body); // html 태그 제거
    $body = mysqli_real_escape_string($this->con, $body);
    // $body = str_replace('\r\n', '\n', $body);
    // $body = nl2br($body);  // nl2br() 함수는 문자열의 모든 줄 바꿈 앞에 HTML 줄 바꿈을 삽입합니다. (<br/> 삽입)

    $checkEmpty = preg_replace('/\s+/', '', $body); // 모든 공백 제거

    if ($checkEmpty != "") {

      $bodyArray = preg_split('/\s+/', $body);

      foreach ($bodyArray as $key => $value) {
        if (strpos($value, "www.youtube.com/watch?v=") !== false) {
          $link = preg_split("!&!", $value);
          $value = preg_replace("!watch\?v=!", "embed/", $link[0]);
          $value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value . "\'></iframe><br>";
          $bodyArray[$key] = $value;
        }
      }

      $body = implode(" ", $bodyArray);

      // 현재 날짜와 시간
      $dateAdded = date('y-m-d h:i:s');;

      // 사용자명 가져오기
      $addedBy = $this->userObj->getUsername();

      // 사용자가 자신의 프로필이라면 userTo는 'none'입니다.
      if ($userTo == $addedBy) {
        $userTo = "none";
      }

      // 게시물 추가
      $insertQuery = mysqli_query($this->con, "INSERT INTO posts(body, added_by, user_to, date_added, user_closed, deleted, likes, image) VALUES('$body', '$addedBy', '$userTo', '$dateAdded', 'no', 'no', '0', '$imageName')");
      $returnedId = mysqli_insert_id($this->con);

      // 알림 추가
      if ($userTo != 'none') {
        $notification = new Notification($this->con, $addedBy);
        $notification->insertNotification($returnedId, $userTo, 'profile_post');
      }

      // 사용자의 게시물 갯수 업데이트
      $numPosts = $this->userObj->getNumPosts();
      $numPosts++;
      $updateQuery = mysqli_query($this->con, "UPDATE users SET num_posts='$numPosts' WHERE username='$addedBy'");

      $stopWords = "게임 노래 댄스 축구";
      $stopWords = preg_split("/[\s,]+/", $stopWords);

      $noPunctuation = preg_replace("/[^a-zA-Z 0-9 ㄱ-ㅎㅏ-ㅣ가-힣]+/", "", $body);

      if (
        strpos($noPunctuation, "height") === false
        && strpos($noPunctuation, "width") === false
        && strpos($noPunctuation, "http") === false
      ) {
        $noPunctuation = preg_split("/[\s,]+/", $noPunctuation);

        foreach ($stopWords as $value) {
          foreach ($noPunctuation as $key => $value2) {
            if ($value == $value2) {
              $noPunctuation[$key] = "";
            }
          }
        }

        foreach ($noPunctuation as $value) {
          $this->calculateTrend($value);
        }
      }
    }
  }

  public function calculateTrend($term)
  {
    echo "<script>console.log('콘솔 남기기 : $term')</script>";
    if ($term != '') {
      $query = mysqli_query($this->con, "SELECT * FROM trends WHERE title='$term'");

      if (mysqli_num_rows($query) == 0) {
        $insertQuery = mysqli_query($this->con, "INSERT INTO trends(title, hits) VALUES('$term', '1')");
      } else {
        $insertQuery = mysqli_query($this->con, "UPDATE trends SET hits=hits+1 WHERE title='$term'");
      }
    }
  }

  public function loadPostsFriends($data, $limit)
  {
    $page = $data['page'];
    $userLoggedIn = $this->userObj->getUsername();

    if ($page == 1) {
      $start = 0;
    } else {
      $start = ($page - 1) * $limit;
    }

    $str = "";
    $dataQuery = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

    if (mysqli_num_rows($dataQuery) > 0) {

      $numIterations = 0;
      $count = 1;

      while ($row = mysqli_fetch_array($dataQuery)) {
        $id = $row['id'];
        $body = $row['body'];
        $addedBy = $row['added_by'];
        $dateTime = $row['date_added'];
        $imagePath = $row['image'];

        // 사용자를 게시하지 않더라도 포함할 수 있도록 user_to 문자열 준비
        if ($row['user_to'] == 'none') {
          $userTo = '';
        } else {
          $userToObj = new User($this->con, $row['user_to']);
          $userToName = $userToObj->getFirstAndLastName();
          $userTo = "to <a href='" . $row['user_to'] . "'>" . $userToName . "</a>";
        }

        // 게시한 사용자가 계정을 닫았는지 확인합니다.
        $addedByObj = new User($this->con, $addedBy);
        if ($addedByObj->isClosed()) {
          continue;
        }

        $userLoggedObj = new User($this->con, $userLoggedIn);
        if ($userLoggedObj->isFriend($addedBy)) {
          if ($numIterations++ < $start) {
            continue;
          }

          // 10개의 게시물이 로드되면 중단됩니다.
          if ($count > $limit) {
            break;
          } else {
            $count++;
          }

          if ($userLoggedIn == $addedBy) {
            $deleteButton = "<button class='delete_button btn-danger' id='post$id'>X</button>";
          } else {
            $deleteButton = "";
          }

          $userDetailsQuery = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username = '$addedBy'");
          $userRow = mysqli_fetch_array($userDetailsQuery);
          $firstName = $userRow['first_name'];
          $lastName = $userRow['last_name'];
          $profilePic = $userRow['profile_pic'];

?>

          <script>
            function toggle<?= $id; ?>() {
              let target = $(event.target);
              if (!target.is("a")) {
                let element = document.getElementById('toggleComment<?= $id; ?>');

                if (element.style.display == 'block')
                  element.style.display = 'none';
                else
                  element.style.display = 'block';
              }
            }
          </script>

        <?php

          $commentsCheck = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
          $commentsCheckNum = mysqli_num_rows($commentsCheck);

          /** 현재 날짜와 게시물 작성 날짜 사이의 년, 월, 일, 시, 분, 초 단위로 시간을 계산한 값 */
          $timeMessage = $this->postWroteDateCalculate($dateTime);

          if ($imagePath != "") {
            $imageDiv = "<div class='postedImage'>
                          <img src='$imagePath'>
                        </div>";
          } else {
            $imageDiv = "";
          }

          $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                    <div class='post_profile_pic'>
                      <img src='$profilePic' width='50'>
                    </div>

                    <div class='posted_by' style='color:#acacac;'>
                      <a href='$addedBy'> $lastName$firstName </a> $userTo &nbsp;&nbsp;&nbsp;&nbsp;$timeMessage
                      $deleteButton
                    </div>

                    <div id='post_body'>
                      $body
                      <br>
                      $imageDiv
                      <br>
                      <br>
                    </div>

                    <div class='newsfeedPostOptions'>
                      댓글($commentsCheckNum)&nbsp;&nbsp;&nbsp;
                      <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                    </div>
                  </div>
                  <div class='post_comment' id='toggleComment$id' style='display:none;'>
                    <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                  </div>
                  <hr>";
        }
        ?>

        <script>
          $(document).ready(function() {
            $('#post<?= $id; ?>').on('click', function() {
              bootbox.confirm("이 게시물을 삭제하시겠습니까?", function(result) {
                $.post("includes/form_handlers/delete_post.php?post_id=<?= $id; ?>", {
                  result: result
                });

                if (result) location.reload();
              })
            })
          });
        </script>

      <?php
      }

      if ($count > $limit) {
        $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'><input type='hidden' class='noMorePosts' value='false'>";
      } else {
        $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'>더 이상 표시할 게시물이 없습니다. </p>";
      }
    }
    echo $str;
  }

  public function loadProfilePosts($data, $limit)
  {
    $page = $data['page'];
    $profileUser = $data['profileUsername'];
    $userLoggedIn = $this->userObj->getUsername();

    if ($page == 1) {
      $start = 0;
    } else {
      $start = ($page - 1) * $limit;
    }

    $str = "";
    $dataQuery = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser') ORDER BY id DESC");

    if (mysqli_num_rows($dataQuery) > 0) {

      $numIterations = 0;
      $count = 1;

      while ($row = mysqli_fetch_array($dataQuery)) {
        $id = $row['id'];
        $body = $row['body'];
        $addedBy = $row['added_by'];
        $dateTime = $row['date_added'];


        if ($numIterations++ < $start) {
          continue;
        }

        // 10개의 게시물이 로드되면 중단됩니다.
        if ($count > $limit) {
          break;
        } else {
          $count++;
        }

        if ($userLoggedIn == $addedBy) {
          $deleteButton = "<button class='delete_button btn-danger' id='post$id'>X</button>";
        } else {
          $deleteButton = "";
        }

        $userDetailsQuery = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username = '$addedBy'");
        $userRow = mysqli_fetch_array($userDetailsQuery);
        $firstName = $userRow['first_name'];
        $lastName = $userRow['last_name'];
        $profilePic = $userRow['profile_pic'];

      ?>

        <script>
          function toggle<?= $id; ?>() {
            let target = $(event.target);
            if (!target.is("a")) {
              let element = document.getElementById('toggleComment<?= $id; ?>');

              if (element.style.display == 'block')
                element.style.display = 'none';
              else
                element.style.display = 'block';
            }
          }
        </script>

        <?php

        $commentsCheck = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
        $commentsCheckNum = mysqli_num_rows($commentsCheck);

        /** 현재 날짜와 게시물 작성 날짜 사이의 년, 월, 일, 시, 분, 초 단위로 시간을 계산한 값 */
        $timeMessage = $this->postWroteDateCalculate($dateTime);

        $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                    <div class='post_profile_pic'>
                      <img src='$profilePic' width='50'>
                    </div>

                    <div class='posted_by' style='color:#acacac;'>
                      <a href='$addedBy'> $lastName$firstName </a> &nbsp;&nbsp;&nbsp;&nbsp;$timeMessage
                      $deleteButton
                    </div>

                    <div id='post_body'>
                      $body
                      <br>
                      <br>
                      <br>
                    </div>

                    <div class='newsfeedPostOptions'>
                      댓글($commentsCheckNum)&nbsp;&nbsp;&nbsp;
                      <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                    </div>
                  </div>
                  <div class='post_comment' id='toggleComment$id' style='display:none;'>
                    <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                  </div>
                  <hr>";
        ?>

        <script>
          $(document).ready(function() {
            $('#post<?= $id; ?>').on('click', function() {
              bootbox.confirm("이 게시물을 삭제하시겠습니까?", function(result) {
                $.post("includes/form_handlers/delete_post.php?post_id=<?= $id; ?>", {
                  result: result
                });

                if (result) location.reload();
              })
            })
          });
        </script>

      <?php
      }

      if ($count > $limit) {
        $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'><input type='hidden' class='noMorePosts' value='false'>";
      } else {
        $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'>더 이상 표시할 게시물이 없습니다. </p>";
      }
    }
    echo $str;
  }


  /** 게시물 작성 날짜와 현재 날짜 사이의 간격을 계산해주는 함수
   * 1. 년
   * 2. 월
   * 3. 일
   * 4. 시
   * 5. 분
   * 6. 초
   */
  public function postWroteDateCalculate($dateTime)
  {
    $dateTimeNow = date('y-m-d h:i:s');
    $startDate = new DateTime($dateTime);
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

    return $timeMessage;
  }

  public function getSinglePost($postId)
  {
    $userLoggedIn = $this->userObj->getUsername();
    $openedQuery = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND like LIKE '%=$postId'");

    $str = "";
    $dataQuery = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$postId'");

    if (mysqli_num_rows($dataQuery) > 0) {
      $row = mysqli_fetch_array($dataQuery);
      $id = $row['id'];
      $body = $row['body'];
      $addedBy = $row['added_by'];
      $dateTime = $row['date_added'];

      // 사용자를 게시하지 않더라도 포함할 수 있도록 user_to 문자열 준비
      if ($row['user_to'] == 'none') {
        $userTo = '';
      } else {
        $userToObj = new User($this->con, $row['user_to']);
        $userToName = $userToObj->getFirstAndLastName();
        $userTo = "to <a href='" . $row['user_to'] . "'>" . $userToName . "</a>";
      }

      // 게시한 사용자가 계정을 닫았는지 확인합니다.
      $addedByObj = new User($this->con, $addedBy);
      if ($addedByObj->isClosed()) {
        return;
      }

      $userLoggedObj = new User($this->con, $userLoggedIn);
      if ($userLoggedObj->isFriend($addedBy)) {
        if ($userLoggedIn == $addedBy) {
          $deleteButton = "<button class='delete_button btn-danger' id='post$id'>X</button>";
        } else {
          $deleteButton = "";
        }

        $userDetailsQuery = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username = '$addedBy'");
        $userRow = mysqli_fetch_array($userDetailsQuery);
        $firstName = $userRow['first_name'];
        $lastName = $userRow['last_name'];
        $profilePic = $userRow['profile_pic'];

      ?>

        <script>
          function toggle<?= $id; ?>() {
            let target = $(event.target);
            if (!target.is("a")) {
              let element = document.getElementById('toggleComment<?= $id; ?>');

              if (element.style.display == 'block')
                element.style.display = 'none';
              else
                element.style.display = 'block';
            }
          }
        </script>

        <?php

        $commentsCheck = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
        $commentsCheckNum = mysqli_num_rows($commentsCheck);

        /** 현재 날짜와 게시물 작성 날짜 사이의 년, 월, 일, 시, 분, 초 단위로 시간을 계산한 값 */
        $timeMessage = $this->postWroteDateCalculate($dateTime);

        $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                    <div class='post_profile_pic'>
                      <img src='$profilePic' width='50'>
                    </div>

                    <div class='posted_by' style='color:#acacac;'>
                      <a href='$addedBy'> $lastName$firstName </a> $userTo &nbsp;&nbsp;&nbsp;&nbsp;$timeMessage
                      $deleteButton
                    </div>

                    <div id='post_body'>
                      $body
                      <br>
                      <br>
                      <br>
                    </div>

                    <div class='newsfeedPostOptions'>
                      댓글($commentsCheckNum)&nbsp;&nbsp;&nbsp;
                      <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                    </div>
                  </div>
                  <div class='post_comment' id='toggleComment$id' style='display:none;'>
                    <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                  </div>
                  <hr>";
        ?>

        <script>
          $(document).ready(function() {
            $('#post<?= $id; ?>').on('click', function() {
              bootbox.confirm("이 게시물을 삭제하시겠습니까?", function(result) {
                $.post("includes/form_handlers/delete_post.php?post_id=<?= $id; ?>", {
                  result: result
                });

                if (result) location.reload();
              })
            })
          });
        </script>

<?php
      } else {
        echo "이 사용자와 친구가 아니므로 이 게시물을 볼 수 없습니다.";
        return;
      }
    } else {
      echo "<p>게시물이 없습니다. 링크를 클릭하면 깨질 수 있습니다.</p>";
      return;
    }
    echo $str;
  }
}
