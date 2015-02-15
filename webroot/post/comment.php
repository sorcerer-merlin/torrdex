<?php
    // Put out the header
    $pageTitle = "Comment";
    require_once(dirname(__FILE__) . '/../include/pieces/header.php');

    // SECURITY: If we are not logged in, you shouldn't be uploading
    if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

    // Check for valid variables, if none then move on with an error
    if (!isset($_POST['mode'])) {
        showError("Invalid parameters. Please contact yoru Administrator.");
    } else {
        $Mode = $_POST['mode'];

        // Let's look for comment adding
        if ($Mode == "add_comment") {
            if (!isset($_POST['hash']) && !isset($_GET['comment_body'])) {
                showError("Invalid parameters. Please contact yoru Administrator.");
            } else {
                $TorrentHash = $_POST['hash'];
                $desctemp = $_POST['comment_body'];

                if ($TorrentHash == "" || $desctemp == "") {
                    showError("Invalid parameters. Please contact yoru Administrator.");
                } else {
                    // If we get here everything should be valid!
                    // Clean up the comment text before trying to insert it
                    $User = $_SESSION['user'];
                    $CommentBody = nl2br(htmlentities($desctemp, ENT_QUOTES, 'UTF-8'));
                    $CommentBody = str_replace(' ', '&nbsp;', $CommentBody);
                    $Time = time();

                    // Let's insert it into the actual database now
                    $result = queryMySQL("INSERT INTO comments(hash,user,body,time) VALUES ('$TorrentHash', '$User', '$CommentBody', '$Time');");
                    if ($result)
                        echo '<script type="text/javascript">window.location = "/details?hash=' . $TorrentHash .'#com"</script>';
                    else
                        showError("There was an error adding your comment. Please contact your Administrator.");
                }
            }
        }
    }

    // Put out the footer
    require_once(dirname(__FILE__) . '../include/pieces/footer.php');
?>
