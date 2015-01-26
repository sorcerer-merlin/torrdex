<?php
	// Put out the header
	$pageTitle = "Upload Torrent";
	require_once('header.php');
	
	// Require the Torrent library
	require_once('php-bittorrent.phar');

	// SECURITY: If we are not logged in, you shouldn't be uploading
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

	// Add our script
    echo <<<_END
  <script type="text/javascript">
    function loadTemplate()
    {
      // get type of torrent we are trying to upload from option box 
      //type = "movie"
      e = O("torrent-type")
  	  type = e.options[e.selectedIndex].text
          
      // get the pass and user here and pass it off 
      request = new ajaxRequest()
      request.open("GET", "templates/" + type + ".template", true)

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
		$TorrentName = $torrent->getName();
		$TorrentType = $_POST["torrent-type"];
		$TorrentHash = $torrent->getHash();
		$TorrentUploaded = $torrent->getCreatedAt(); //date("Y-m-d @ h:ia", $torrent->getCreatedAt());
		$TorrentFiles = $torrent->getFileList();
		$TorrentComment = $torrent->getComment();
		$TorrentMagnet = `transmission-show -m "/var/www/html/tordex/$target_file"`;
		$TorrentTotalSize = $torrent->getSize();
		$TorrentAuthor = $_SESSION['user'];
		
		// Parse the torrent description so we can save new lines and also convert all special chars to
		// HTML entities
		$desctemp = $_POST["torrent-desc"];
		$TorrentDesc = nl2br(htmlentities($desctemp, ENT_QUOTES, 'UTF-8'));
		
		// Parse the Files list into something we can put into our DB
		$TorrentFileList = "";
		$TorrentFileCount = 0;
		foreach ($TorrentFiles as $File) {
			$TorrentFileList = $TorrentFileList . "- " . $File['path'][0] . " (" . humanFileSize($File['length']) . ")<br>";
			$TorrentFileCount = $TorrentFileCount + 1;
		}
		$TorrentFileList = EscapeQuotes($TorrentFileList);
		
		/* DEBUG: print out all of the torrent information before we drop it into the DB
		echo "<div align='left'>";
		echo "Name:     " . $TorrentName . "<br>";
		echo "Hash:     " . $TorrentHash . "<br>";
		echo "Type:     " . $TorrentType . "<br>";
		echo "Created:  " . $TorrentUploaded . "<br>";
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
		echo "Magnet:   " . $TorrentMagnet . "<br>";
		echo "</div>";*/
		
		// Delete the torrent file, as we don't really want to keep it
		//unlink($target_file);
		
		// CHANGED: Move the torrent file to rename it by hash
		rename($target_file, $target_dir . $TorrentHash . ".torrent");
		
		// Make the SQL query
		$queryString = "INSERT INTO torrents VALUES" .
		 "('$TorrentName', '$TorrentHash', '$TorrentType', '$TorrentUploaded', '$TorrentFileList', '$TorrentComment', '$TorrentDesc', '$TorrentMagnet', '$TorrentTotalSize', '$TorrentFileCount', '$TorrentAuthor')";
		//echo $queryString;
		
		// Submit the SQL query
        $result = queryMySQL($queryString);
        if (!$result) {
			echo "<h3>Ooops!!!</h3><br><b><u>MySQL Error:</b></u> <i>" . mysql_error() . "<i>";	
		} else {
			echo "<h3>Success!</h3><br>Your torrent has been <b>successfully</b> uploaded, you can view it <a href='details.php?hash=$TorrentHash'>here</a>!";
		}
		
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
        <td class="rowcap" width="168px">Type:</td>
        <td class="rowdata">
        <select class="select-style" name="torrent-type" id="torrent-type" width="200px">
        	<option>Application</option>
            <option>Game</option>
            <option>Movie</option>
            <option>TV</option>
            <option>Music</option>
            <option selected="selected">Other</option>
        </select>
        &nbsp;&nbsp;&nbsp;<input type="submit" value="Description Template" name="submit_small" id="submit_small" form="" onclick="loadTemplate()">
        </td>
    </tr>
    <tr>
        <td class="rowcap">Description:</td>
        <td class="rowdata">
        <textarea id="torrent-desc" name="torrent-desc" rows="20" cols="100" placeholder="Brief description of your torrent... Or to start with a Description Template, click the button above!" required="required"></textarea>
        </td>
    </tr>
    </table><br /><br />
    <input type="submit" value="Upload Torrent" name="submit" id="submit">
</form>
<?php
	}
	
	// Put out the footer
	require_once('footer.php');
?>