<?php
	// Grab our functions (mySQL, etc.)
	require_once 'functions.php';

	if (isset($_POST['user'])) {
		$user = $_POST['user'];
		$target_dir = "avatars/";
		unlink($target_dir . $user . ".jpg");
		echo "<img src='img/default_avatar.jpg' width='100' height='100' ALT='Avatar'>";
	}
?>
