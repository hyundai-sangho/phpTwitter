<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/sns/includes/header.php';

$profileId = $user['username'];
$imgSrc = "";
$resultPath = "";
$msg = "";

/****************************************
 * 0 - 임시 이미지가 존재하면 제거하십시오.
 ****************************************/
if (!isset($_POST['x']) && !isset($_FILES['image']['name'])) {
  // 사용자의 임시 이미지를 삭제합니다.
  $tempPath = 'assets/images/profile_pics/' . $profileId . '_temp.jpeg';
  if (file_exists($tempPath)) {
    // https://stackoverflow.com/questions/3621215/what-does-mean-in-php
    @unlink($tempPath);
  }
}

if (isset($_FILES['image']['name'])) {
  /****************************************
   * 1 - 원본 이미지를 서버에 업로드하십시오.
   ****************************************/
  // 이름 | 크기 | 임시 위치
  $ImageName = $_FILES['image']['name'];
  $ImageSize = $_FILES['image']['size'];
  $ImageTempName = $_FILES['image']['tmp_name'];

  // 파일 확장자
  $ImageType = @explode('/', $_FILES['image']['type']);
  $type = $ImageType[1]; // file type

  // 업로드 폴더
  $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/sns/assets/images/profile_pics';

  // 파일명
  $fileTempName = $profileId . '_original' . md5(time()) . 'n' . $type; // 임시 파일명
  $fullPath = $uploadDir . "/" . $fileTempName;                                 // 임시 파일 경로
  $fileName = $profileId . "_temp.jpeg";                                        // $profileId.'_temp.'.$type; // 최종 크기의 이미지의 경우
  $fullPath2 = $uploadDir . "/" . $fileName;                                    // 이미지 파일 경로

  // 파일을 올바른 위치로 이동
  $move = move_uploaded_file($ImageTempName, $fullPath);
  chmod($fullPath, 0777);

  // 유효한 업로드인지 확인
  if (!$move) {
    die('파일이 업로드되지 않았습니다.');
  } else {
    $imgSrc = "assets/images/profile_pics/" . $fileName; // 자르기 영역에 표시할 이미지
    $msg = "업로드 완료";                                 // 화면에 메시지 표시
    $src = $fileName;
  }

  /****************************************
   * 2 - 자르기 영역에 맞게 이미지를 크기를 조정하십시오.
   ****************************************/

  // 업로드된 이미지 크기를 가져옵니다.
  clearstatcache();
  $originalSize = getimagesize($fullPath);
  $originalWidth = $originalSize[0];
  $originalHeight = $originalSize[1];

  // 새 크기를 지정합니다.
  $mainWidth = 500;                                              // 이미지의 너비를 설정하십시오.
  $mainHeight = $originalHeight / ($originalWidth / $mainWidth); // 이것은 높이가 비율을 설정합니다.

  // 올바른 PHP 기능을 사용하여 새 이미지를 만듭니다.
  if ($_FILES['image']['type'] == 'image/gif') {
    $src2 = imagecreatefromgif($fullPath);
  } elseif ($_FILES['image']['type'] == 'image/jpeg' || $_FILES['image']['type'] == 'image/pjpeg') {
    $src2 = imagecreatefromjpeg($fullPath);
  } elseif ($_FILES['image']['type'] == 'image/png') {
    $src2 = imagecreatefrompng($fullPath);
  } else {
    $msg .= "파일을 업로드하는 중에 오류가 발생했습니다. 업로드할 파일은 .jpg, .gif or .png 파일만 사용하세요. <br/>";
  }

  // 크기가 조정된 새 이미지 생성
  $main = imagecreatetruecolor($mainWidth, $mainHeight);
  imagecopyresampled($main, $src2, 0, 0, 0, 0, $mainWidth, $mainHeight, $originalWidth, $originalHeight);

  // 새 버전 업로드
  $mainTemp = $fullPath2;
  imagejpeg($main, $mainTemp, 90);
  chmod($mainTemp, 0777);

  // 메모리 해제
  imagedestroy($src2);
  imagedestroy($main);
  @unlink($fullPath); // 원본 업로드 삭제

}

/****************************************
 * 3 - 이미지 자르기 및 JPG로 변환
 ****************************************/
if (isset($_POST['x'])) {

  // 게시된 파일 형식
  $type = $_POST['type'];

  // 이미지 소스
  $src = 'assets/images/profile_pics/' . $_POST['src'];
  $filename = $profileId . md5(time());

  if ($type == 'jpg' || $type == 'jpeg' || $type == 'JPG' || $type = 'JPEG') {

    // 대상 크기 150 x 150
    $targetWidth = 150;
    $targetHeight = 150;

    // 출력 품질
    $jpegQuality = 90;

    // 자른 이미지 사본 생성
    $imgR = imagecreatefromjpeg($src);
    $dstR = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopyresampled($dstR, $imgR, 0, 0, $_POST['x'], $_POST['y'], $targetWidth, $targetHeight, $_POST['w'], $_POST['h']);

    // 자른 새 버전 저장
    imagejpeg($dstR, "assets/images/profile_pics/" . $finalname . "n.jpeg", 90);
  } elseif ($type == 'png' || $type == 'PNG') {
    // 대상 크기 150 x 150
    $targetWidth = 150;
    $targetHeight = 150;

    // 출력 품질
    $jpegQuality = 90;

    // 자른 이미지 사본 생성
    $imgR = imagecreatefrompng($src);
    $dstR = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopyresampled($dstR, $imgR, 0, 0, $_POST['x'], $_POST['y'], $targetWidth, $targetHeight, $_POST['w'], $_POST['h']);

    // 자른 새 버전 저장
    imagejpeg($dstR, "assets/images/profile_pics/" . $finalname . "n.jpeg", 90);
  } elseif ($type == 'gif' || $type == 'GIF') {
    // 대상 크기 150 x 150
    $targetWidth = 150;
    $targetHeight = 150;

    // 출력 품질
    $jpegQuality = 90;

    // 자른 이미지 사본 생성
    $imgR = imagecreatefromgif($src);
    $dstR = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopyresampled($dstR, $imgR, 0, 0, $_POST['x'], $_POST['y'], $targetWidth, $targetHeight, $_POST['w'], $_POST['h']);

    // 자른 새 버전 저장
    imagejpeg($dstR, "assets/images/profile_pics/" . $finalname . "n.jpeg", 90);
  }

  // 메모리 해제
  imagedestroy($imgR);
  imagedestroy($dstR);
  @unlink($src);

  // 자른 이미지를 페이지로 되돌리기
  $resultPath = "assets/images/profile_pics/" . $finalname . "n.jpeg";

  // 데이터베이스에 이미지 삽입
  $insertPicQuery = mysqli_query($con, "UPDATE users SET profile_pic='$resultPath' WHERE username='$userLoggedIn'");
  header("Location: " . $userLoggedIn);
}
?>

<div id="Overlay" style="width: 100%; height:100%; border:0px #990000 solid; position:absolute; top:0px; left:0px; z-index:2000; display:none;"></div>
<div class="main_column column">
  <div id="forExample">
    <p><b><?= $msg; ?></b></p>

    <form action="upload.php" method="post" enctype="multipart/form-data">
      Upload something<br /><br />
      <input type="file" id="image" name="image" style="width: 200px; height: 30px;" /><br /><br />
      <input type="submit" value="Submit" style="width:85px; height:25px;" />
    </form>
  </div>

  <?php
  if ($imgSrc) { // 이미지가 업로드된 경우 자르기 영역 표시
  ?>
    <script>
      $('#Overlay').show();
      $('#formExample').hide();
    </script>
    <div id="CroppingContainer" style="width:800px; max-height: 600px; background-color: #fff; margin-left: -200px; position: relative; overflow:hidden; border:2px #666 solid; z-index:2001; padding-bottom: 0px;">
      <div id="CroppingArea" style="width:500px; max-height:400px; position: relative; overflow:hidden; margin: 40px 0px 40px 40px; border: 2px #666 solid; float:left;">
        <img src="<?= $imgSrc; ?>" border="0" id="jcrop_target" style="border:0px #990000 solid; position: relative; margin: 0px 0px 0px 0px; padding:0px;" alt="">
      </div>

      <div id="InfoArea" style="width:180px; height:150px; position:relative; overflow:hidden; margin:40px 0px 0px 40px; border:0px #666 solid; float:left;">
        <p style="margin:0px; padding:0px; color:#444; font-size:18px;">
          <b>프로필 이미지 자르기</b><br /><br />
          <span style="font-size:14px;">
            업로드한 프로필 이미지 자르기/크기 조정. <br />
            프로필 이미지가 만족스러우면 저장을 클릭하세요.
          </span>
        </p>
      </div>

      <br />

      <div id="CropImageForm" style="width:100px; height:30px; float:left; margin:10px 0px 0px 40px;">
        <form action="upload.php" method="post" onsubmit="return checkCoords();">
          <input type="hidden" id="x" name="x" />
          <input type="hidden" id="y" name="y" />
          <input type="hidden" id="w" name="w" />
          <input type="hidden" id="h" name="h" />
          <input type="hidden" value="jpeg" name="type" /> <?php // $type
                                                            ?>
          <input type="hidden" value="<?= $src ?>" name="src" />
          <input type="submit" value="Save" style="width:100px; height:30px;" />
        </form>
      </div>

      <div id="CropImageForm2" style="width:100px; height:30px; float:left; margin:10px 0px 0px 40px;">
        <form action="upload.php" method="post" onsubmit="return cancelCrop();">
          <input type="submit" value="Cancel Crop" style="width:100px; height:30px;" />
        </form>
      </div>

    </div><!-- CroppingContainer -->
  <?php
  } ?>
</div>





<?php if ($resultPath) {
?>

  <img src="<?= $resultPath ?>" style="position:relative; margin:10px auto; width:150px; height:150px;" />

<?php } ?>


<br /><br />
