<?php

// includes
require_once('include/mysql.php');
require_once('include/udptscraper.php');
require_once('include/httptscraper.php');

// Get the scrapers ready to go
$TimeOut = 5;
$MaxRead = 1024 * 4; // 4MB
$UDPscraper = new udptscraper($TimeOut);
$HTTPscraper = new httptscraper($TimeOut,$MaxRead);

// Get Torrent Scrape Info function
function scrapeTorrent($hash,$trackers)
{
	// Init variables for return
	$Arr['seeders'] = 0;
	$Arr['leechers'] = 0;
	$Arr['downloads'] = 0;
	
	// globals
	global $UDPscraper;
	global $HTTPscraper;

	if (substr($trackers, 0, 3) == "udp")
		$ret = $UDPscraper->scrape($trackers,array($hash));
	else
		$ret = $HTTPscraper->scrape($trackers, array($hash));

	// get values
	$Arr['seeders'] = $ret[$hash]['seeders'];
	$Arr['leechers'] = $ret[$hash]['leechers'];
	$Arr['downloads'] = $ret[$hash]['completed'];
		
	// return an array of the results of the scraping process
	return($Arr);
}

// Run through the actual scraping process
$result = queryMySQL("SELECT * FROM torrents ORDER BY uploaded DESC");

// Go through each one and print it out
while($row = $result->fetch_object()) { 
	// Get the info we need from the Torrent that we just uploaded
	$Name = $row->name;
	$Hash = $row->hash;
	$Trackers = $row->trackers;
	$WorkingTracker = $row->working_tracker;
	
	echo "Name:" . $Name . PHP_EOL;
	echo "Hash: " . $Hash . PHP_EOL;
	echo "Trackers: " . $Trackers . PHP_EOL;
	echo "Working: " . $WorkingTracker . PHP_EOL;
	echo "--------------------------------------------------" . PHP_EOL;

	// If we already have a working tracker on file, let's start there.
	// If NOT, loop through the array of trackers from the torrent file
	// and go from there.
	
	$TrackerWorked = "";
	if ($WorkingTracker != "NOT_YET" && $WorkingTracker != "") {
		// Scrape the current tracker for the torrent info
		try {
			$ArrayResult = scrapeTorrent($Hash, $WorkingTracker);
		}catch(ScraperException $e){
			echo '*** Tracker Error (' . $WorkingTracker . '): ' . $e->getMessage() . PHP_EOL;
			//echo('Connection error: ' . ($e->isConnectionError() ? 'yes' : 'no') . "<br />\n");
			
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
				$ArrayResult = scrapeTorrent($Hash, $Tracker);
			}catch(ScraperException $e){
				echo '*** Tracker Error (' . $Tracker . '): ' . $e->getMessage() . PHP_EOL;
				//echo('Connection error: ' . ($e->isConnectionError() ? 'yes' : 'no') . "<br />\n");
				
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
	echo PHP_EOL;
	if ($ArrayResult['seeders'] != 0 && $ArrayResult['leechers'] != 0 && $ArrayResult['downloads'] != 0) {
		echo "< o > (" . $TrackerWorked . ") worked" . PHP_EOL;
		print_r($ArrayResult);
		
		// Do the actual adding to the DB
		$Seeders = $ArrayResult['seeders'];
		$Leechers = $ArrayResult['leechers'];
		$Downloads = $ArrayResult['downloads'];
		$Now = time();
		$update_result = queryMySQL("UPDATE torrents SET scrape_date='$Now', seeders='$Seeders', leechers='$Leechers', download_count='$Downloads', working_tracker='$TrackerWorked' WHERE hash='$Hash';");
		if (!$update_result) echo "*** MySQL Error: Couldnt update torrent stats in DB" . PHP_EOL;
	} else {
		echo "*** No valid trackers ***" . PHP_EOL;
	}
	echo "--------------------------------------------------" . PHP_EOL;
	print PHP_EOL . PHP_EOL;

} // end while loop

?>
