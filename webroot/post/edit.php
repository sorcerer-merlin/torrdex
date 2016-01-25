<?php

    // Put out the header
    $pageTitle = "Edit Torrent";
    require_once(dirname(__FILE__) . '/../include/pieces/header.php');

    // TODO: make sure the torrent exists, make sure
    // we have changed something to even update (<--- that last part maybe silly IDK performance reasons?!)

    // Check to see if user is logged in
    if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

    //if (($row->author == $_SESSION['user'] && $configOptions_Booleans['admin_only_removes'] == "false") || $_SESSION['acct_type'] == ACCT_TYPE_ADMIN) {

    if (isset($_POST['info_hash']) && isset($_POST['new_name']) && isset($_POST['torrent-type']) && isset($_POST['new_desc'])) {
        // Get the values
        $TorrentHash = $_POST['info_hash'];
        $TorrentName = EscapeQuotes($_POST['new_name']);
        $TorrentType = $_POST['torrent-type'];
        $TorrentDesc = htmlentities($_POST['new_desc'], ENT_QUOTES, 'UTF-8');
        $TorrentAuthor = getTorrentAuthor();

        if (($TorrentAuthor != NULL) && ($TorrentAuthor == $_SESSION['user']) && ($configOptions_Booleans['admin_only_removes'] == "false") || $_SESSION['acct_type'] == ACCT_TYPE_ADMIN) {
            /* DEBUG
            echo $TorrentHash . "<br>";
            echo $TorrentName . "<br>";
            echo $TorrentType . "<br>";
            echo $TorrentDesc . "<br>"; */

            // Make and submit the query.
            $query = "UPDATE torrents SET name='$TorrentName', type='$TorrentType', description='$TorrentDesc' WHERE hash='$TorrentHash';";
            $result = queryMySQL($query);

            // Check the results, should be OK but error checking is a good practice
            if ($result)
                echo '<script type="text/javascript">window.location = "/details?hash=' . $TorrentHash . '"</script>';
            else
                showError('That torrent does not exist, or you do not have permission to edit it! Please contact your Administrator.');
        } else
            showError('There was an error. Please contact your Administrator.');
    } else {
        showError('Missing parameters. Please contact your Administrator.');
    }

    // Put out the footer
    require_once(dirname(__FILE__) . '../include/pieces/footer.php');
?>
