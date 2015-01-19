<?php
	// Grab our functions (mySQL, etc.)
	require_once 'functions.php';

	if (isset($_POST['user']) && isset($_POST['fullname'])) {
		$user = $_POST['user'];
		$newfullname = $_POST['fullname'];
		$_SESSION['fullname'] = $newfullname; // <--- Leaving here for coding purposes, however for some reason this line doesn't seem to do as Advertised. Had to add a name change check hack to header.php! :P

		$queryString = "UPDATE members SET fullname='$newfullname' WHERE user='$user';";	
		$result = queryMySQL($queryString);
        if (!$result) {
			echo  "<span class='error'>&nbsp;&#x2718; Error!</span>";
		} else {
			echo "<span class='available'>&nbsp;&#x2714; Saved!</span>";
		}
	}
?>
