<?php
	// Grab our functions (mySQL, etc.)
	require_once(dirname(__FILE__) . '/../include/functions.php');
	require_once(dirname(__FILE__) . '/../include/libs/PasswordHash/PasswordHash.php');

	if (isset($_POST['user']) && isset($_POST['pass'])) {
		$user = $_POST['user'];
		$pass = $_POST['pass'];
		$PasswordHashed = PasswordHash::create_hash($pass);	
		$_SESSION['pass'] = $pass; // <--- Leaving here for coding purposes, however for some reason this line doesn't seem to do as Advertised. TODO: Add password hack to header page (how since DB version will be hashed?!)


		$queryString = "UPDATE members SET pass='$PasswordHashed' WHERE user='$user';";	
		$result = queryMySQL($queryString);
        if (!$result) {
			echo  "<span class='error'>&nbsp;&#x2718; Error!</span>";
		} else {
			echo "<span class='available'>&nbsp;&#x2714; Password Changed!</span>";
		}
	}
?>
