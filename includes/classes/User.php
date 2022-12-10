<?php

class User
{
  private $user;
  private $con;

  public function __construct($con, $user)
  {
    $this->con = $con;
    $userDetailsQuery = mysqli_query($con, "SELECT * FROM users WHERE username='$user'");
    $this->user = mysqli_fetch_array($userDetailsQuery);
  }

  public function getUsername()
  {
    return $this->user['username'];
  }

  public function getNumberOfFriendRequests()
  {
    $username = $this->user['username'];
    $selectQuery = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$username'");
    return mysqli_num_rows($selectQuery);
  }

  public function getNumPosts()
  {
    $username = $this->user['username'];
    $selectQuery = mysqli_query($this->con, "SELECT num_posts FROM users WHERE username='$username'");
    $row = mysqli_fetch_array($selectQuery);

    return $row['num_posts'];
  }

  public function getFirstAndLastName()
  {
    $username = $this->user['username'];
    $selectQuery = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE username='$username'");
    $row = mysqli_fetch_array($selectQuery);

    return $row['last_name'] . $row['first_name'];
  }

  /** 프로필 이미지 가져오는 함수  */
  public function getProfilePic()
  {
    $username = $this->user['username'];
    $selectQuery = mysqli_query($this->con, "SELECT profile_pic FROM users WHERE username='$username'");
    $row = mysqli_fetch_array($selectQuery);

    return $row['profile_pic'];
  }

  public function getFriendArray()
  {
    $username = $this->user['username'];
    $selectQuery = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$username'");
    $row = mysqli_fetch_array($selectQuery);

    return $row['friend_array'];
  }

  public function isClosed()
  {
    $username = $this->user['username'];
    $selectQuery = mysqli_query($this->con, "SELECT user_closed FROM users WHERE username='$username'");
    $row = mysqli_fetch_array($selectQuery);

    return $row['user_closed'] == 'yes';
  }

  public function isFriend($usernameToCheck)
  {
    $usernameComma = "," . $usernameToCheck . ",";

    // true 또는 false 리턴
    return strstr($this->user['friend_array'], $usernameComma) || $usernameToCheck == $this->user['username'];
  }

  public function didReceiveRequest($userFrom)
  {
    $userTo = $this->user['username'];
    $checkRequestQuery = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$userTo' AND user_from='$userFrom'");

    return mysqli_num_rows($checkRequestQuery) > 0;
  }

  public function didSendRequest($userTo)
  {
    $userFrom = $this->user['username'];
    $checkRequestQuery = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$userTo' AND user_from='$userFrom'");

    return mysqli_num_rows($checkRequestQuery) > 0;
  }

  public function removeFriend($userToRemove)
  {
    $loggedInUser = $this->user['username'];

    $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$userToRemove'");
    $row = mysqli_fetch_array($query);
    $friendArrayUsername = $row['friend_array'];

    $newFriendArray = str_replace($userToRemove . ",", "",  $this->user['friend_array']);
    $removeFriend = mysqli_query($this->con, "UPDATE users SET friend_array='$newFriendArray' WHERE username='$loggedInUser'");

    $newFriendArray = str_replace($this->user['username'] . ",", "", $friendArrayUsername);
    $removeFriend = mysqli_query($this->con, "UPDATE users SET friend_array='$newFriendArray' WHERE username='$userToRemove'");
  }

  public function sendRequest($userTo)
  {
    $userFrom = $this->user['username'];
    $query = mysqli_query($this->con, "INSERT INTO friend_requests(user_to, user_from) VALUES('$userTo', '$userFrom')");
  }

  public function getMutualFriends($userToCheck)
  {
    $mutualFriends = 0;
    $userArray = $this->user['friend_array'];
    $userArrayExplode = explode(",", $userArray);

    $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$userToCheck'");
    $row = mysqli_fetch_array($query);
    $userToCheckArray = $row['friend_array'];
    $userToCheckArrayExplode = explode(",", $userToCheckArray);

    foreach ($userArrayExplode as $i) {
      foreach ($userToCheckArrayExplode as $j) {
        if ($i == $j && $i != "") {
          $mutualFriends++;
        }
      }
    }

    return $mutualFriends;
  }
}
