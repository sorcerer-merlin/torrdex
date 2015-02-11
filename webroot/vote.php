<?php
    // Grab our functions (mySQL, etc.)
    require_once 'functions.php';

    if (isset($_POST['hash']) && isset($_POST['vote']) && isset($_POST['user'])) {
        $User = $_POST['user'];
        $Hash = $_POST['hash'];
        $Vote = $_POST['vote'];
        $Time = time();
     
        // Fix the vote variable for the DB entry
        if ($Vote == "up") $Vote = "+"; elseif ($Vote = "dn") $Vote = "-";

        // TODO: Check for hash and vote and user not be blank, need to be logged in, haven't already voted,
        //       torrent exists, etc.

        // Check if they have already voted for this torrent. If not then add their vote. If so, see if they voted opposite
        // and if so change it.
        if (!hasVoted($User, $Hash)) {
            // Run the query to add to the DB
            $result = queryMySQL("INSERT INTO ratings(hash,user,vote,time) VALUES ('$Hash', '$User', '$Vote', '$Time');");
        } else {
            // if the current vote on file doesn't match the vote they are submitting then switch the vote.
            if (getVote($User, $Hash) != $Vote)
                $result = queryMySQL("UPDATE ratings SET vote='$Vote', time='$Time' WHERE user='$User' AND hash='$Hash';");
        }

        // TODO: Some kind of error checking

        // Now we need to get the total number of votes for this torrent again
        $UpVotes = getVotes($Hash, "+");
        $DownVotes = getVotes($Hash, "-");
        echo <<<_END
            <td><span id="votes_up">+$UpVotes</span></td>
            <td>&nbsp;</td>
            <td><span id="votes_dn">-$DownVotes</span></td>
_END;

    }
?>
