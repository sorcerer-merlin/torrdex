<?php 
	$pageTitle = "Logout";
    require_once(dirname(__FILE__) . '/include/pieces/header.php');

	// SECURITY: If we are not logged in, you can't logout
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

	// Destroy the session and let them know they have logged out.
    destroySession();
    //echo "<h3>Goodbye!</h3><br>You have been logged out. Have a great day!";
	echo '<script type="text/javascript">window.location = "/"</script>';
			
	require_once(dirname(__FILE__) . '/include/pieces/footer.php');
?>
