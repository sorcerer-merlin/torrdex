<?php
	// Grab our functions (mySQL, etc.)
	require_once(dirname(__FILE__) . '/../include/functions.php');
	require_once(dirname(__FILE__) . '/../include/libs/PasswordHash/PasswordHash.php');

	if (isset($_POST['user']) && isset($_POST['pass'])) {
		$user = $_POST['user'];
		$pass   = $_POST['pass'];

		$result = queryMysql("SELECT * FROM members WHERE user='$user'");
		if ($result->num_rows) {
			$row = $result->fetch_object();
			$StoredHash = $row->pass;
			if (PasswordHash::validate_password($pass, $StoredHash))
	  			echo  "<span class='available'>&nbsp;&#x2714; Old Password is correct.</span>";
	  		else
	  			echo "<span class='error'>&nbsp;&#x2718; Old Password is incorrect.</span>";
	    }
	}
?>
