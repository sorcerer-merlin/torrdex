<?php
	// Grab our functions (mySQL, etc.)
	require_once(dirname(__FILE__) . '/../include/functions.php');
	

	if (isset($_POST['user']) && isset($_POST['fullname']) && isset($_POST['acct_type']) && isset($_POST['certified']) && isset($_POST['login'])) {
		// Pull the information passed to the script thru POST
		$user = $_POST['user'];
		$fullname = $_POST['fullname'];
		$acct_type = $_POST['acct_type'];
		$certified = $_POST['certified'];
		$login = $_POST['login'];
		$error = "";

		// Now let's pull the current version of the account for our conditional testing
		$result = queryMySQL("SELECT user,pass,fullname,acct_type FROM members WHERE user='$user'");
		if ($result->num_rows == 0) {
        	$error = "error";
        } else {
        	$row = $result->fetch_object();
        	$old_fullname = $row->fullname;
        	$old_acct_type = $row->acct_type;

        	// If they decided to change the user's name, let's do that first
        	if ($user != $login) {
        		$result = queryMySQL("UPDATE members SET user='$login' WHERE user='$user';");
        		if (!$result) $error = "error";
        		$user = $login; // added to make sure the next set of queries work, tho we might flip flop
        	}
        	// Change the Display name if they changed it
        	if ($fullname != $old_fullname) {
        		$result = queryMySQL("UPDATE members SET fullname='$fullname' WHERE user='$user';");
        		if (!$result) $error = "error";
        	}
        	// Change the account type if they changed it
        	if ($acct_type != $old_acct_type) {
        		$result = queryMySQL("UPDATE members SET acct_type='$acct_type' WHERE user='$user';");
        		if (!$result) $error = "error";
        	}
        	// Check for certified uploader status and change accordingly
        	if ($certified == "true") {
        		// We are showing that they are NOT certified and want to be, so fix it
        		if (!isCertified_BOOL($user)) {
        			$result = queryMySQL("INSERT INTO certified VALUES ('$user');");
        			if (!$result) $error = "error";
        		}
        	} elseif ($certified == "false") {
        		if (isCertified_BOOL($user)) {
        			$result = queryMySQL("DELETE FROM certified WHERE user='$user';");
        			if (!$result) $error = "error";
        		}
        	}
        }
		
        if ($error == "error") {
			echo  "<span class='error'>&nbsp;&#x2718; Error!</span>";
		} else {
			echo "<span class='available'>&nbsp;&#x2714; Saved!</span>";
		}
	}
?>

