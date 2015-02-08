<?php
    // Put out the header
    $pageTitle = "Comment";
    require_once('header.php');

    // SECURITY: If we are not logged in, you shouldn't be uploading
    if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

    // Check for valid variables, if none then move on with an error
    if (!isset($_GET['hash']) && !isset($_GET['id'])) {
        showError("Invalid parameters. Please contact your Administrator.");
    } else {
        $TorrentHash = $_GET['hash'];
        $CommentID = $_GET['id'];

        // Check for empty variables
        if ($TorrentHash == "" || $CommentID == "") {
            showError("Invalid parameters. Please contact your Administrator.");
        } else {
            // Check to make sure the comment exists
            $result = queryMySQL("SELECT user FROM comments where id='$CommentID';");
            if ($result->num_rows == 0) {
                showError("That comment doesn't exist! Please contact your Administrator.");
            } else {
                // Check to see if we own the comment, or we are an ADMIN
                if ($Author == $_SESSION['user'] || $_SESSION['acct_type'] == ACCT_TYPE_ADMIN) {
                    $result = queryMySQL("DELETE FROM comments WHERE id='$CommentID';");
                    if ($result)        
                        echo '<script type="text/javascript">window.location = "/details?hash=' . $TorrentHash . '#com"</script>';
                    else
                        showError("There was an error deleting the comment. Please contact your Administrator.");
                } else
                    showError("You do not have permission to do that! Please contact your Administrator.");
            }
        }
    }

    // Put out the footer
    require_once('footer.php');
?>
