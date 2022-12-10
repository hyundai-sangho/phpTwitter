<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/header.php';

if (isset($_POST['post'])) {
  $uploadOk = 1;
  $imageName = $_FILES['fileToUpload']['name'];
  $errorMessage = "";

  if ($imageName != "") {
    $targetDir = "assets/images/posts";
    $imageName = $targetDir . uniqid() . basename($imageName);
    $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

    if ($_FILES['fileToUpload']['size'] > 10000000) {
      $errorMessage = "파일이 너무 큽니다.";
      $uploadOk = 0;
    }

    if (
      strtolower($imageFileType) != "jpeg" &&
      strtolower($imageFileType) != "jpg" &&
      strtolower($imageFileType) != "png"
    ) {
      $errorMessage = "죄송합니다. jpeg, jpg 및 png 파일만 허용됩니다.";
      $uploadOk = 0;
    }

    if ($uploadOk) {
      if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
        // 이미지 업로드 완료
      } else {
        // 이미지 업로드 실패
        $uploadOk = 0;
      }
    }
  }

  if ($uploadOk) {
    $post = new Post($con, $userLoggedIn);
    $post->submitPost($_POST['post_text'], 'none', $imageName);
  }else{
    echo "<div style='text-align: center;' class='alert alert-danger'>
            $errorMessage
          </div>";
  }

  header("Location: index.php");
}

?> <div class="user_details column">
  <a id="userName" href="<?= $userLoggedIn; ?>" data-userName="<?= $userLoggedIn; ?>"><img src="<?= $user['profile_pic']; ?>" alt="프로필 사진"></a>
  <div class="user_details_left_right">
    <a href="<?= $userLoggedIn; ?>"><?= $user['username']; ?></a>
    <br>
    <span><?= "게시물: " . $user['num_posts']; ?></span>
    <br>
    <span><?= "좋아요: " . $user['num_likes']; ?></span>
  </div>
</div>
<div class="main_column column">
  <form class="post_form" action="index.php" method="post" enctype="multipart/form-data">
    <input type="file" name="fileToUpload" id="fileToUpload">
    <textarea name="post_text" id="post_text" placeholder="할 말이 있습니까?"></textarea>
    <input type="submit" name="post" id="post_button" value="등록">
    <hr>
  </form>
  <div class="posts_area"></div>
  <img id="loading" src="assets/images/icons/loading.gif" alt="로딩 이미지">
</div>

<?php
$query = mysqli_query($con, "SELECT * FROM trends ORDER BY hits DESC LIMIT 9");
if (mysqli_num_rows($query) !== 0) {
?>
  <div class="user_details column">
    <div class="trends">
      <?php
      foreach ($query as $row) {
        $word = $row['title'];
        // 한글은 1글자에 3개를 잡아먹는다. 15는 총 5글자에 해당.
        $wordDot = strlen($word) >= 15 ? "..." : "";
        $trimmedWord = str_split($word, 15);
        $trimmedWord = $trimmedWord[0];

        echo "<div style='padding: 1px'>";
        echo $trimmedWord . $wordDot;
        echo "<br></div>";
      }
      ?>
    </div>
  </div>
<?php } ?>
<script src="assets/js/ajaxLoadPosts.js"></script>
</body>

</html>
