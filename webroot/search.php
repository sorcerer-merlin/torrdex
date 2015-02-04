<?php
	$pageTitle = "Search";
	require_once 'header.php';
    
	// SECURITY: If we are not logged in, you shouldn't be uploading
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "login?page=search"</script>';
	
	// check for keywords or display form
	if (!isset($_GET['keywords']) && !isset($_GET['type'])) {
?>
<!-- the search form -->
        <form method='get' action='search'>
        <!--<input type='text' maxlength='50' size='60' name='keywords' placeholder="Torrent Keywords..." autofocus="autofocus" required="required">-->
        <input type='search' results='5' autosave='tordex_search_autosave' maxlength='50' size='60' name='keywords' placeholder="Torrent Keywords..." autofocus="autofocus" required="required"><br>
        <input type="radio" id="type_all" name="type" value="-1" checked><label for="type_all"><span><span></span></span>All</label>&nbsp;&nbsp;&nbsp;&nbsp;

        <?php 
            foreach ($TorrentTypes as $key => $value) {
                echo "<input type='radio' id='type_" . $value . "' name='type' value='" . $key . "'><label for='type_" . $value . "'><span><span></span></span>" . $value . "</label>&nbsp;&nbsp;&nbsp;&nbsp;";
            }
        ?>        

        <br><br><input type='submit' value='Search...' id='submit'>
        </form>
<!-- end search form -->
<?php		
	} else {
	
    // Pagination. Check to see if they specified a start page number, or not. Fix it.
    $LimitPerPage = $configOptions_Integers['torr_per_page'];
    if (!isset($_GET['offset']) or !is_numeric($_GET['offset'])) {
      //we give the value of the starting row to 0 because nothing was found in URL
      $offset = 0;
    //otherwise we take the value from the URL
    } else {
      $offset = (int)$_GET['offset'];
    }

    if ($configOptions_Booleans['enable_pagination'] == "true")
        $Paging = " LIMIT " . $offset . "," . $LimitPerPage;
    else
        $Paging = "";

	// Do the search from the database
    // UPDATE: We added type searches now, so check the type being submitted and then go from there
    $SearchHeading = "";
    if ($_GET['type'] == -1) {
        $result = queryMySQL("SELECT * FROM torrents WHERE  name LIKE '%" . $_GET['keywords'] . "%' ORDER BY uploaded DESC" . $Paging . ";");
        $SearchHeading = "Search Results";
    } else {
        $result = queryMySQL("SELECT * FROM torrents WHERE  name LIKE '%" . $_GET['keywords'] . "%' AND type='" . $_GET['type'] . "' ORDER BY uploaded DESC" . $Paging . ";");
        $SearchHeading = "Search Results in " . $TorrentTypes[$_GET['type']];
    }
		
	// Check to make sure we have it in the database before continuing
	if ($result->num_rows == 0) {
		showError("Your search terms did not yield any results! <a href='search'>Try again</a>?");
	} else {
?>
        <h3><?php print $SearchHeading ?></h3>
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
				<table align="left">
					<tr>
					<td>
						<img src="img/type_icons/<?php print $TorrentType; ?>.png" ALT="<?php print $TorrentType; ?>" width="16px" height="16px">
					</td>
					<td>&nbsp;</td>
					<td>
						<a href="listby?mode=type&param=<?php print $row->type; ?>"><?php print $TorrentType; ?></a>
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
        <br>
<?php
if ($configOptions_Booleans['enable_pagination'] == "true") {
    $NextLink = '<a href="'. $_SERVER['PHP_SELF'] .'?keywords=' . $_GET['keywords'] .'&type=' . $_GET['type'] .'&offset='.($offset+$LimitPerPage).'">Next &gt;&gt;</a>';
    $PrevLink = '<a href="'. $_SERVER['PHP_SELF'] .'?keywords=' . $_GET['keywords'] .'&type=' . $_GET['type'] .'&offset='.($offset-$LimitPerPage).'">&lt;&lt; Prev</a>';
    if ($offset > 0) echo $PrevLink . "&nbsp;&nbsp;&nbsp;&nbsp;";
    if (($offset+$LimitPerPage) < countTorrentsSearch($_GET['type'], $_GET['keywords'])) echo $NextLink;
}
?>
        <br><br>

<?php
	}  // end if block
	}
?>
	</td>
  </tr>
<?php  
    require_once 'footer.php';
?>