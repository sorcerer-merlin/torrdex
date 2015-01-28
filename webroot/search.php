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
        <input type='search' results='5' autosave='tordex_search_autosave' maxlength='50' size='60' name='keywords' placeholder="Torrent Keywords..." autofocus="autofocus" required="required"><br>
        <input type="radio" id="type_all" name="type" value="all" checked><label for="type_all"><span><span></span></span>All</label>&nbsp;&nbsp;
        <input type="radio" id="type_app" name="type" value="app"><label for="type_app"><span><span></span></span>App</label>&nbsp;&nbsp;
        <input type="radio" id="type_game" name="type" value="game"><label for="type_game"><span><span></span></span>Game</label>&nbsp;&nbsp;
        <input type="radio" id="type_movie" name="type" value="movie"><label for="type_movie"><span><span></span></span>Movie</label>&nbsp;&nbsp;
        <input type="radio" id="type_tv" name="type" value="tv"><label for="type_tv"><span><span></span></span>TV</label>&nbsp;&nbsp;
        <input type="radio" id="type_music" name="type" value="music"><label for="type_music"><span><span></span></span>Music</label>&nbsp;&nbsp;
        <input type="radio" id="type_other" name="type" value="other"><label for="type_other"><span><span></span></span>Other</label>
        <br><br><input type='submit' value='Search...' id='submit'>
        </form>
<!-- end search form -->
<?php		
	} else {
	
	// Do the search from the database
	$result = queryMySQL("SELECT * FROM torrents WHERE  name LIKE '%" . $_GET['keywords'] . "%' ORDER BY uploaded DESC;");
		
	// Check to make sure we have it in the database before continuing
	if ($result->num_rows == 0) {
		showError("Your search terms did not yield any results! <a href='search'>Try again</a>?");
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
		$TorrentType = $TorrentTypes[$row->type];
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
        	<td class="rowdata">
				<table align="right">
					<tr>
					<td>
						<img src="img/type_icons/<?php print $TorrentType; ?>.png" ALT="<?php print $TorrentType; ?>" width="16px" height="16px">
					</td>
					<td>&nbsp;</td>
					<td>
						<?php print $TorrentType; ?>
					</td>
					</tr>
				</table>        		
        	</td>
            <td class="rowdata" width="300px"><a href="details?hash=<?php print $TorrentHash; ?>"><?php print $TorrentName; ?></a></td>
            <td class="rowdata" style="text-align:right;"><?php print $TorrentAge; ?></td>
            <td class="rowdata" style="text-align:center;"><?php print humanFileSize($TorrentSize); ?></td>
            <td class="rowdata" style="text-align:center;"><?php print $TorrentFileCount; ?></td>
            <td class="rowdata" style="text-align:right;" >
            	<table align="right">
            		<tr>
            		<td>
		            	 <?php
			            	if ($TorrentAuthor != "Anonymous") {
			            		print "<a href='author?name=$row->author'>$TorrentAuthor</a>";
			            	} else {
			            		print $TorrentAuthor; 
			            	}
			             ?>
            		</td>
            		<td>&nbsp;</td>
            		<td>
            			<?php print isCertified($row->author); ?>	
            		</td>
            		</tr>
            	</table>
             </td>
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