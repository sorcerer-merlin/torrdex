<?php
	// Put out the header
	$pageTitle = "Upload Torrent";
	require_once(dirname(__FILE__) . '/include/pieces/header.php');
	
	// Require the Torrent library
	require_once(dirname(__FILE__) . '/include/libs/BitTorrent/php-bittorrent.phar');

    // Require the scrape library
    require_once(dirname(__FILE__) . '/include/libs/scrape/jscrape.php');

	// SECURITY: If we are not logged in, you shouldn't be uploading
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

	// Add our script
    echo <<<_END
  <script type="text/javascript">
    function toggleNameBox()
    {
        e = O("torrent-name")
        from_torrent = e.options[e.selectedIndex].value
        if (from_torrent == 'true')
            O("namebox").innerHTML = "&nbsp;"
        else
            O("namebox").innerHTML = "<input type='text' maxlength='72' required='required' id='custom' name='custom' placeholder='Custom Name' width='200px'>";
    }
    function loadTemplate()
    {
      // get type of torrent we are trying to upload from option box 
      e = O("torrent-type")
  	  type = e.options[e.selectedIndex].text
          
      // get the pass and user here and pass it off 
      params  = "type=" + type
      request = new ajaxRequest()
      request.open("POST", "post/desc.php", true)
	  request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
      request.onreadystatechange = function()
      {
        if (this.readyState == 4)
          if (this.status == 200)
            if (this.responseText != null)
              O('torrent-desc').value = this.responseText
      }
      request.send(params) 
    }
    function ajaxRequest()
    {
      try { var request = new XMLHttpRequest() }
      catch(e1) {
        try { request = new ActiveXObject("Msxml2.XMLHTTP") }
        catch(e2) {
          try { request = new ActiveXObject("Microsoft.XMLHTTP") }
          catch(e3) {
            request = false
      } } }
      return request
    }
  </script>
_END;

	// We are multi-function upload script so first we need to see if they have uploaded anything
	if(isset($_POST["submit"])) {
		// They have uploaded a file, process here. First let's identify the directory we are
		// putting it in, and the filename to be used.
		$target_dir = "uploads/";
		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
		
		// Save the torrent file (from the POST data in the form) to the specified directory & filename
		move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
		
		// Get the info we need from the Torrent that we just uploaded
		$torrent = PHP\BitTorrent\Torrent::createFromTorrentFile($target_file);

        // Let's see if they passed a custom name, of if we are pulling it from the torrent's info
        // block (using double-nested if block to stop errors/warnings for undefined variable!)
        $TorrentName = "";
        if (isset($_POST['custom']))
            if ($_POST['custom'] != "")
                $TorrentName = EscapeQuotes($_POST['custom']);

        // Check to see if we set the variable above, if not let's set it to the torrent's info name now
        if ($TorrentName == "") $TorrentName = EscapeQuotes($torrent->getName());

        // Get the rest of the Torrent info
		$TorrentType = $_POST["torrent-type"];
		$TorrentHash = $torrent->getHash();
		$TorrentUploaded = time(); 
        $TorrentCreated = $torrent->getCreatedAt();
		$TorrentFiles = $torrent->getFileList();
		$TorrentComment = EscapeQuotes($torrent->getComment());
		$TorrentTotalSize = $torrent->getSize();
		$TorrentAuthor = $_SESSION['user'];

        // Fix for a blank time when the torrent was created (as in the client didn't specify).
        // FUTURE: check for valid time?
        if ($TorrentCreated == "")
            $TorrentCreated = time();

		// Grab the trackers list (or single tracker if not multiple listed in torrent) and URL encode for use in MAGNET link
		if ($torrent->getAnnounceList() == "") {
			$TorrentTrackers =  $torrent->getAnnounce();
			$TorrentTrackerList = "&tr=" . rawurlencode($TorrentTrackers);
            $TorrentTrackersDB = $TorrentTrackers;
		} else {
			$TorrentTrackers = $torrent->getAnnounceList();
			$TorrentTrackerList = "";
            $TorrentTrackersDB = "";
			foreach ($TorrentTrackers as $Tracker) {
				//$TorrentTrackerList .= "- " . $Tracker[0] . "<br>";
				$TorrentTrackerList .= "&tr=" . rawurlencode($Tracker[0]);
                $TorrentTrackersDB .= $Tracker[0] . ",";
			}
		}

        // Do the scrape part here
        $ArrayResult = 0;
        $Seeders = 0;
        $Leechers = 0;
        $Downloads = 0;
        $ScrapeTime = time();
        $TorrentTrackersDB = EscapeQuotes($TorrentTrackersDB);
        $WorkingTracker = "NOT_YET";

		// Comment shouldn't be NULL
		if ($TorrentComment == "") $TorrentComment = "No Comment";
		
		// Manually create the magnet link using the info provided by the torrent
		/* Magnet URI: magnet:?xt=urn:sbtih:YNCKHTQCWBTRNJIV4WNAE52SJUQCZO5C (valid)

		  Display Name .... (dn): 
		  eXact Length .... (xl): 
		  eXact Topic ..... (xt): urn:btih:YNCKHTQCWBTRNJIV4WNAE52SJUQCZO5C
		  Acceptable Source (as): 
		  eXact Source .... (xs): 
		  Keyword Topic ... (kt): 
		  Manifest Topic .. (mt): 
		  address TRacker . (tr): */
		//$TorrentMagnetOld = `transmission-show -m "/var/www/html/torrdex/$target_file"`;
		$TorrentMagnet = "magnet:?xt=urn:btih:" . $TorrentHash . "&dn=" . rawurlencode($TorrentName) . $TorrentTrackerList;


		// Parse the torrent description so we can save new lines and also convert all special chars to
		// HTML entities
		$desctemp = $_POST["torrent-desc"];
		$TorrentDesc = htmlentities($desctemp, ENT_QUOTES, 'UTF-8');
		//$TorrentDesc = nl2br(htmlentities($desctemp, ENT_QUOTES, 'UTF-8'));
		//$TorrentDesc = str_replace(' ', '&nbsp;', $TorrentDesc);
		
		// Parse the Files list into something we can put into our DB
		$TorrentFileList = "";
		$TorrentFileCount = 0;
		if (is_array($TorrentFiles)) {
			foreach ($TorrentFiles as $File) {
				$TorrentFileList = $TorrentFileList . "- " . $File['path'][0] . " (" . humanFileSize($File['length']) . ")<br>";
				$TorrentFileCount = $TorrentFileCount + 1;
			}
		} else {
			$TorrentFileList = "- " . $TorrentFiles . " (" . humanFileSize($TorrentTotalSize) . ")<br>";
			$TorrentFileCount = 1;
		}
		$TorrentFileList = EscapeQuotes($TorrentFileList);
		
		/*DEBUG: print out all of the torrent information before we drop it into the DB
		echo "<div align='left'>";
		echo "Name:     " . $TorrentName . "<br>";
		echo "Hash:     " . $TorrentHash . "<br>";
		echo "Type:     " . $TorrentType . "<br>";
		echo "Created:  " . $TorrentUploaded . "<br>";
		echo "Trackers:<Br>";
		echo "<blockquote>";
		//print_r($TorrentTrackers);
		echo $TorrentTrackerList;
		echo "</blockquote>";
		echo "Files:    <br>";
		echo "<blockquote><pre>";
		//print_r($TorrentFiles);
		echo $TorrentFileList ;
		echo "</pre></blockquote><br>";
		echo "Comment:  " . $TorrentComment . "<br>";
		echo "Desc:     ";
		echo "<blockquote>";
		echo $TorrentDesc;
		echo "</blockquote><br>";
		echo "Magnet:   <br>";
		echo $TorrentMagnet . "<br>";
		echo "</div>";*/
		
		// Delete the torrent file, as we don't really want to keep it
		//unlink($target_file);
		
		// CHANGED: Move the torrent file to rename it by hash
		rename($target_file, $target_dir . $TorrentHash . ".torrent");
		
		// Check if the torrent already exists in the DB (by info-hash) before adding it -- also NOTE: It will not enter in to the DB anyway, as we have a UNIQUE key index on the hash key anyway!
		if (!isTorrentinDatabase($TorrentHash)) {
			// Torrent is NOT in the database
			// Make the SQL query
			$queryString = "INSERT INTO torrents VALUES" .
			 "('$TorrentName', '$TorrentHash', '$TorrentType', '$TorrentUploaded', '$TorrentFileList', '$TorrentComment', '$TorrentDesc', '$TorrentMagnet', '$TorrentTotalSize', '$TorrentFileCount', '$TorrentAuthor', '$TorrentCreated', '$TorrentTrackersDB', '$ScrapeTime', '$Seeders', '$Leechers', '$Downloads', '$WorkingTracker')";
			//echo $queryString;
			
			// Submit the SQL query
	        $result = queryMySQL($queryString);

            // UDPATED: Now do the initial torrent scrape for seeders/leechers/downloads stats
            scrapeTorrent($TorrentHash);

	        //if ($result)
				echo "<h3>Success!</h3><br>Your torrent has been <b>successfully</b> uploaded, you can view it <a href='details.php?hash=$TorrentHash'>here</a>!";
		} else
			showError("This torrent is already in our database (by info-hash). Please contact an administrator.");
	} else {
		// No file yet, so lets show the form
?>
<!-- Only displayed if they haven't uploaded a file yet -->
<form action="upload" method="post" enctype="multipart/form-data">
    <!-- Torrent Information -->
    <table width="992px">
    <tr>
        <td class="rowcap" width="168px">Torrent:</td>
        <td class="rowdata"><input type="file" name="fileToUpload" id="fileToUpload" required="required"></td>
    </tr>
    <tr>
        <td class="rowcap">Name:</td>
        <td class="rowdata">
        <table>
        <tr>
            <td>
                <select class="select-style" name="torrent-name" id="torrent-name" width="100px" onchange="toggleNameBox()">
                    <option value='true' selected='selected'>From Torrent</option>
                    <option value='false'>Custom</option>
                </select>
            </td>
            <td>&nbsp;</td>
            <td><span id="namebox"></span></td>
        </tr>
        </table>
        </td>
    </tr>
    <tr>
        <td class="rowcap" width="168px">Type:</td>
        <td class="rowdata">
        <select class="select-style" name="torrent-type" id="torrent-type" width="200px">
	        <?php
	        	foreach ($TorrentTypes as $key => $value) {
		    		echo "<option value='$key'";
		    		if ($key == DEFAULT_TORR_TYPE) echo " selected='selected'";
		    		echo ">$value</option>";
			    }
	        ?>
        </select>
        &nbsp;&nbsp;&nbsp;<input type="submit" value="Description Template" name="submit_small" id="submit_small" form="" onclick="loadTemplate()">
        &nbsp;&nbsp;&nbsp;<a href="http://help.twitch.tv/customer/portal/articles/839490-markdown-basics" onClick="return popup(this, 'help')">MarkDown Basics (Help)</A>
        </td>
    </tr>
    <tr>
        <td class="rowcap">Description:</td>
        <td class="rowdata">
        <textarea id="torrent-desc" name="torrent-desc" rows="20" cols="100" placeholder="Brief description of your torrent... Or to start with a Description Template, click the button above! MarkDown syntax is supported, see link above for help." required="required"></textarea>
        </td>
    </tr>
    </table><br /><br />
    <input type="submit" value="Upload Torrent" name="submit" id="submit">
</form>
<?php
	}
	
	// Put out the footer
	require_once(dirname(__FILE__) . '/include/pieces/footer.php');
?>
