<?php
if (isset( $_FILES['media_file'] )) {

  $targetPath = 'uploads/';
  $path_to_thumbs_directory = 'uploads/thumbs/';
  $final_width_of_image = 350;
  $filename     = $_FILES['media_file']['name'];
  $tmpName  = $_FILES['media_file']['tmp_name'];
  $error    = $_FILES['media_file']['error'];
  $size     = $_FILES['media_file']['size'];
  $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  $target_file = $targetPath . basename($_FILES["media_file"]["name"]);
  $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  move_uploaded_file($tmpName, $targetPath . basename($_FILES["media_file"]["name"]) );
  if(preg_match('/[.](jpg)$/', $filename)) {
      $im = imagecreatefromjpeg($targetPath . $filename);
  } else if (preg_match('/[.](gif)$/', $filename)) {
      $im = imagecreatefromgif($targetPath . $filename);
  } else if (preg_match('/[.](png)$/', $filename)) {
      $im = imagecreatefrompng($targetPath . $filename);
  }
  $thumbsAray = ['300' , '600' , '1200'];
  $ox = imagesx($im);
  $oy = imagesy($im);


  # Create Thumbnails here
  foreach ($thumbsAray as $key => $value) {
    $nx = $value;
    $ny = floor($oy * ($value / $ox));
    $nm = imagecreatetruecolor($nx, $ny);
    imagecopyresized($nm, $im, 0,0,0,0,$nx,$ny,$ox,$oy);
    imagejpeg($nm, $path_to_thumbs_directory .$value.'-'. $filename);
    // imagedestroy($im);
  }
  # Create Thumbnails here
 
}
 ?>
