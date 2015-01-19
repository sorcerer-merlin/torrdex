<?php

	// Put out the header
	$pageTitle = "Remove Torrent";
	require_once('header.php');

	// SECURITY: If we are not logged in, you shouldn't be removing a torrent
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

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
				echo "<h3>Ooops!!!</h3><br>There was an error removing your Torrent from the database!<br><br>";	
			}
			
			// Delete the file
			$deleted = unlink("uploads/" . $TorrentHash . ".torrent");
			if (!$deleted) {
				echo "<h3>Ooops!!!</h3><br>There was an error removing your Torrent file!<br><br>";
			}
			
			// We made it this far, no issues
			echo "<h3>Success!</h3><br>Your Torrent was successfully removed from our databases.<br><br>";
		}
	}

	require_once('footer.php');

?>