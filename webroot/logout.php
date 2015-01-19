<?php 
	require_once 'header.php';

	// SECURITY: If we are not logged in, you can't logout
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

	// Destroy the session and let them know they have logged out.
    destroySession();
    echo "<h3>Goodbye!</h3><br>You have been logged out. Have a great day!";
	echo '<script type="text/javascript">window.location = "/"</script>';
			
	require_once 'footer.php';
?>