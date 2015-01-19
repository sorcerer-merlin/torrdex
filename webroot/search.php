<?php
	$pageTitle = "Search";
	require_once 'header.php';
    
	// SECURITY: If we are not logged in, you shouldn't be uploading
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "login?page=search"</script>';
	
	// check for keywords or display form
	if (!isset($_GET['keywords'])) {
?>
<!-- the search form -->
        <form method='get' action='search'>
        <!--<input type='text' maxlength='50' size='60' name='keywords' placeholder="Torrent Keywords..." autofocus="autofocus" required="required">-->
        <input type='search' results='5' autosave='tordex_search_autosave' maxlength='50' size='60' name='keywords' placeholder="Torrent Keywords..." autofocus="autofocus" required="required">
        <br><br><input type='submit' value='Search...' id='submit'>
        </form>
<!-- end search form -->
<?php		
	} else {
	
	// Do the search from the database
	$result = queryMySQL("SELECT * FROM torrents WHERE  name LIKE '%" . $_GET['keywords'] . "%' ORDER BY uploaded DESC;");
		
	// Check to make sure we have it in the database before continuing
	if ($result->num_rows == 0) {
		echo "<h3>Ooops!!!</h3><Br><br>Your search terms did not yield any results! <a href='search'>Try again</a>?";
	} else {
?>
        <h3>Search Results</h3>
        <table width="90%" class="sortable">
        <tr>
        	<td class="rowcap">Type:</td>
            <td class="rowcap" width="50%" style="text-align:center;">Name:</td>
            <td class="rowcap">Age:</td>
            <td class="rowcap">Size:</td>
            <td class="rowcap">Files:</td>
            <td class="rowcap">Author:</td>
        </tr>
        
<?php

		// Go through each one and print it out
	// Go through each one and print it out
	while($row = $result->fetch_object()) { 
		$TorrentType = $row->type;
		$TorrentName = $row->name;
		//$TorrentUploaded = $row->uploaded;
		$TorrentHash = $row->hash;
		$TorrentAuthor = "Anonymous";
		$TorrentSize = $row->size;
		$TorrentFileCount = $row->filecount;
		$TorrentAge = dateDiff(time(), intval($row->uploaded), 1); 
		$TorrentAuthor = getDisplayName($row->author);
?>

        <tr>
        	<td class="rowdata"><?php print $TorrentType; ?></td>
            <td class="rowdata" width="300px"><a href="details?hash=<?php print $TorrentHash; ?>"><?php print $TorrentName; ?></a></td>
            <td class="rowdata" style="text-align:right;"><?php print $TorrentAge; ?></td>
            <td class="rowdata" style="text-align:center;"><?php print humanFileSize($TorrentSize); ?></td>
            <td class="rowdata" style="text-align:center;"><?php print $TorrentFileCount; ?></td>
            <td class="rowdata" style="text-align:right;" ><?php print $TorrentAuthor; ?>&nbsp;<?php print isCertified($row->author); ?></td>
        </tr>

<?php
	    } // end while loop
	
?>               
        </table>

<?php
	}  // end if block
	}
?>
	</td>
  </tr>
<?php  
    require_once 'footer.php';
?>