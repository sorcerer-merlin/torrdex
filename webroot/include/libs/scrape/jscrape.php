<?php

// include the scrape libs
require_once(dirname(__FILE__) . '/udptscraper.php');
require_once(dirname(__FILE__) . '/httptscraper.php');

// Get the scrapers ready to go
$TimeOut = 5;
$MaxRead = 1024 * 4; // 4MB
$UDPscraper = new udptscraper($TimeOut);
$HTTPscraper = new httptscraper($TimeOut,$MaxRead);

// Get torrent info from the tracker via scrape
function scrapeTracker($hash,$tracker)
{
    // Init variables for return
    $Arr['seeders'] = 0;
    $Arr['leechers'] = 0;
    $Arr['downloads'] = 0;
    
    // globals
    global $UDPscraper;
    global $HTTPscraper;

    if (substr($tracker, 0, 3) == "udp")
        $ret = $UDPscraper->scrape($tracker,array($hash));
    else
        $ret = $HTTPscraper->scrape($tracker, array($hash));

    // get values
    $Arr['seeders'] = $ret[$hash]['seeders'];
    $Arr['leechers'] = $ret[$hash]['leechers'];
    $Arr['downloads'] = $ret[$hash]['completed'];
        
    // return an array of the results of the scraping process
    return($Arr);
}

// Find torrent info from DB before doing actual scrape of tracker
function scrapeTorrent($Hash)
{
    // Grab the torrent that we are specifying in the variable
    $result = queryMySQL("SELECT * FROM torrents WHERE hash='$Hash'");

    // If we don't have a valid row from the DB return NULL
    if (!$result) return(NULL);
    if ($result->num_rows == 0) return(NULL);

    // Get the torrent info
    $row = $result->fetch_object();
    $Trackers = $row->trackers;
    $WorkingTracker = $row->working_tracker;
    $ScrapeDate = $row->scrape_date;
    
    // If we already have a working tracker on file, let's start there.
    // If NOT, loop through the array of trackers from the torrent file
    // and go from there.
    
    $TrackerWorked = "";
    if ($WorkingTracker != "NOT_YET" && $WorkingTracker != "") {
        // Scrape the current tracker for the torrent info
        try {
            $ArrayResult = scrapeTracker($Hash, $WorkingTracker);
        }catch(ScraperException $e){
            //echo '*** Tracker Error (' . $WorkingTracker . '): ' . $e->getMessage() . PHP_EOL;
            //echo('Connection error: ' . ($e->isConnectionError() ? 'yes' : 'no') . "<br />\n");

            // This tracker doesn't work so let's make sure we make the array 0
            $ArrayResult['seeders'] = 0;
            $ArrayResult['leechers'] = 0;
            $ArrayResult['downloads'] = 0;
        }
        
        // If we get a result don't mess with the rest of the trackers
        if ($ArrayResult['seeders'] != 0 && $ArrayResult['leechers'] != 0 && $ArrayResult['downloads'] != 0) {
            $TrackerWorked = $WorkingTracker;
        }       
    }
    
    if ($TrackerWorked == "") {
        // Build an array from the tracker string in the DB
        $TrackerArray = explode(",", $Trackers);

        // Step the through the trackers and get the info, once we find it
        // drop the rest of the foreach loop and make note of the tracker
        // that worked
        foreach ($TrackerArray as $Tracker) {
            // Scrape the current tracker for the torrent info
            try {
                $ArrayResult = scrapeTracker($Hash, $Tracker);
            }catch(ScraperException $e){
                //echo '*** Tracker Error (' . $Tracker . '): ' . $e->getMessage() . PHP_EOL;
                //echo('Connection error: ' . ($e->isConnectionError() ? 'yes' : 'no') . "<br />\n");
                
                // This tracker doesn't work so let's make sure we make the array 0
                $ArrayResult['seeders'] = 0;
                $ArrayResult['leechers'] = 0;
                $ArrayResult['downloads'] = 0;
            }
            
            // If we get a result don't mess with the rest of the trackers
            if ($ArrayResult['seeders'] != 0 && $ArrayResult['leechers'] != 0 && $ArrayResult['downloads'] != 0) {
                $TrackerWorked = $Tracker;
                break;
            }
        }
    }
    
    // We have a working tracker, so let them know what it is and also what the torrent stats are...
    if ($ArrayResult['seeders'] != 0 && $ArrayResult['leechers'] != 0 && $ArrayResult['downloads'] != 0) {        
        // Do the actual adding to the DB
        $Seeders = $ArrayResult['seeders'];
        $Leechers = $ArrayResult['leechers'];
        $Downloads = $ArrayResult['downloads'];
        $Now = time();
        $update_result = queryMySQL("UPDATE torrents SET scrape_date='$Now', seeders='$Seeders', leechers='$Leechers', download_count='$Downloads', working_tracker='$TrackerWorked' WHERE hash='$Hash';");

        // Send back the HTML info for the page
        $SeedersComma = number_format($Seeders);
        $LeechersComma = number_format($Leechers);
        $DownloadsComma = number_format($Downloads);
        $msg = <<<_END
                <span class="torrent_info">($DownloadsComma downloads)</span>
                <table>
                    <tr>
                        <td><span class="seeders_label">Seeders:</span></td>
                        <td>&nbsp;</td>
                        <td style="text-align:right;"><span class="seeders_number">$SeedersComma</span></td>
                        <td rowspan="2">&nbsp;&nbsp;&nbsp;</td>
                        <td rowspan="2"><span class="tooltip" title="Click to Refresh Stats!"><img class="vote" src="img/refresh_button.png" width="32px" height="32px" ALT="Refresh Stats" onclick="doStats()"></span></td>
                    </tr>
                    <tr>
                        <td><span class="leechers_label">Leechers:</span></td>
                        <td>&nbsp;</td>
                        <td style="text-align:right;"><span class="leechers_number">$Leechers</span></td>
                    </tr>
                </table>
_END;
        //if (!$update_result) echo "*** MySQL Error: Couldnt update torrent stats in DB" . PHP_EOL;
    } else {
        $msg = "<span class='error'>There are no working trackers as of " . date("Y-m-d @ h:ia",$ScrapeDate) . ".</span>";
    }
    //echo "--------------------------------------------------" . PHP_EOL;
    //print PHP_EOL . PHP_EOL;
    return($msg);
}