<?php

    require_once(dirname(__FILE__) . '/../include/functions.php');
    require_once(dirname(__FILE__) . '/../include/libs/scrape/jscrape.php');

    if (isset($_POST['hash'])) {
        $Hash = $_POST['hash'];

        // Check to see if the hash is blank, keep going.
        // TODO: should probably check for a valid torrent hash in DB. But anyway.
        if ($Hash != "") {
            $retval = scrapeTorrent($Hash);
            echo $retval;
            #echo $Hash;
        }
    }

?>