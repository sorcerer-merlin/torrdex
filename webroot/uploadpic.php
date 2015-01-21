<?php
	// Grab our functions (mySQL, etc.)
	require_once 'functions.php';

	//echo $_POST['user'] . "=" . $_FILES['imageToUpload']['name'];

	if (isset($_POST['user'])) {
		$user = $_POST['user'];
		$target_dir = "avatars/";
		$saveto = $target_dir . $user . ".jpg";
		// do the whole resize bit and save it as a new 
		move_uploaded_file($_FILES['imageToUpload']['tmp_name'], $saveto);
	    $typeok = TRUE;

	    switch($_FILES['imageToUpload']['type'])
	    {
	      case "image/gif":   $src = imagecreatefromgif($saveto); break;
	      case "image/jpeg":  // Both regular and progressive jpegs
	      case "image/pjpeg": $src = imagecreatefromjpeg($saveto); break;
	      case "image/png":   $src = imagecreatefrompng($saveto); break;
	      default:            $typeok = FALSE; break;
	    }

	    if ($typeok)
	    {
		  list($w, $h) = getimagesize($saveto);

	      /*$max = 100;
	      $tw  = $w;
	      $th  = $h;

	      if ($w > $h && $max < $w)
	      {
	        $th = $max / $w * $h;
	        $tw = $max;
	      }
	      elseif ($h > $w && $max < $h)
	      {
	        $tw = $max / $h * $w;
	        $th = $max;
	      }
	      elseif ($max < $w)
	      {
	        $tw = $th = $max;
	      } */

	      $tmp = imagecreatetruecolor(100, 100); //$tmp = imagecreatetruecolor($tw, $th);
	      imagecopyresampled($tmp, $src, 0, 0, 0, 0, 100, 100, $w, $h);
	      //imageconvolution($tmp, array(array(-1, -1, -1), array(-1, 16, -1), array(-1, -1, -1)), 8, 0);
	      imagejpeg($tmp, $saveto);
	      imagedestroy($tmp);
	      imagedestroy($src);
	    }
		//echo "<span class='available'>&nbsp;&#x2714; Saved!</span>";
		echo "<img src='$target_dir$user.jpg' width='100' height='100' ALT='Avatar'>";
	}
?>
