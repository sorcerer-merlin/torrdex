<?php
	// Grab our functions (mySQL, etc.)
	require_once 'functions.php';
	

	if (isset($_POST['user'])) {
		$user = $_POST['user'];
		$result = queryMySQL("DELETE FROM members where user='$user';");
		//$result = true;
		if (!$result) {
			echo  "<span class='error'>&nbsp;&#x2718; Error!</span>";
		} else {
			echo 'success';
		}
	}

?>
