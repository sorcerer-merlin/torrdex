<?php

	// Put out the header
	$pageTitle = "Remove Torrent";
	require_once(dirname(__FILE__) . '/../include/pieces/header.php');

	// SECURITY: If we are not logged in, you shouldn't be removing a torrent
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

	// TODO: check validity of torrent-hash, maybe even write a function that does it in functions.php

	$TorrentHash = $_POST['torrent-hash'];
	
	//DEBUG: echo $TorrentHash;

	//Check (via mySQL query) that torrent belongs to this user, even though we are using the more
	// secure POST variables. If it belongs to them, remove it and then let them know it has been removed,
	// or there was an error.
	$queryString = "SELECT author FROM torrents WHERE hash='$TorrentHash';";
	$result = queryMysql($queryString);
	if ($result->num_rows ==0) {
		// hash doesn't exist
		echo '<script type="text/javascript">window.location = "/"</script>';
	} else {
		// hash does exist, check to see if author matches logged-in user via SESSION variable
		$row = $result->fetch_object();
		if ($row->author == $_SESSION['user'] || $_SESSION['acct_type'] == ACCT_TYPE_ADMIN) {
			// TODO: Run the SQL query to remove it from the DB. Remove the actual torrent file from the 
			// uploads dir. Output to the user what we did or error and then done.
			$queryString = "DELETE FROM torrents WHERE hash='$TorrentHash';";
			$result = queryMysql($queryString);
			if (!$result) {
				// failed to delete the torrent from the DB
				showError("There was an error removing your torrent from the database. Please contact your Administrator.");
			}
			
			// Delete the file
			$deleted = unlink("uploads/" . $TorrentHash . ".torrent");
			if (!$deleted) {
				showError("There was an error removing your .torrent file from the uploads. Please contact your Administrator.");
			}

			// Remove all of the comments
			$result = queryMySQL("DELETE FROM comments WHERE hash='$TorrentHash';");
			if (!$result) {
				showError("There was an error removing the torrent's comments. Please contact your Administrator.");
			}
			
			// We made it this far, no issues
			echo "<h3>Success!</h3><br>Your Torrent was successfully removed from our databases.<br><br>";
		}
	}

	// Put out the footer
	require_once(dirname(__FILE__) . '../include/pieces/footer.php');
?>
