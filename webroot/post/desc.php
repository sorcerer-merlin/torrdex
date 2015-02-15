<?php
	require_once(dirname(__FILE__) . '/../include/functions.php');
	

	if (isset($_POST['type'])) {
		// Load the file, and echo to the screen
		$Template = $_POST['type'];
		$FileContents = file_get_contents("../templates/" . $Template . ".template");
		echo $FileContents;
	}
?>
