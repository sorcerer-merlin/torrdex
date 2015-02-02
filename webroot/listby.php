<?php
	$pageTitle = "List By";
	require_once 'header.php';
	$ListHeading = "";
    
	// SECURITY: If we are not logged in, you shouldn't be uploading
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "login?page=search"</script>';
	
	// check for keywords or display form
	if (!isset($_GET['mode']) && !isset($_GET['param'])) {
		showError("No terms were passed to list Torrents by.");
	} else {
		$Mode = $_GET['mode'];

        // Pagination. Check to see if they specified a start page number, or not. Fix it.
        $LimitPerPage = TORRENTS_PER_PAGE;
        if (!isset($_GET['offset']) or !is_numeric($_GET['offset'])) {
          //we give the value of the starting row to 0 because nothing was found in URL
          $offset = 0;
        //otherwise we take the value from the URL
        } else {
          $offset = (int)$_GET['offset'];
        }
		
		// We want to show all torrents by an author, using the exact name supplied
        $Where = "";
        $ListHeading = "Browse All Torrents";
		if ($Mode == "author") {
			$Author = $_GET['param'];
			$Where = "WHERE author = '$Author'";
			$ListHeading = "Listing Torrents Uploaded by " . getDisplayName($Author);
		}

		// We want to show all torrents by the type specified
		if ($Mode == "type") {
			$Type = $_GET['param'];
			$Where = "WHERE type = '$Type'";
			$ListHeading = "Listing Torrents by Type (" . $TorrentTypes[$Type] . ")";
		}

        // Use pagination or not
        if ($configOptions['enable_pagination'] == "true")
            $Paging = " LIMIT " . $offset . "," . $LimitPerPage;
        else
            $Paging = "";

        // Do the actual query put together
        $result = queryMySQL("SELECT * FROM torrents " . $Where . " ORDER BY uploaded DESC" . $Paging  . ";");
			
		// Check to make sure we have it in the database before continuing
		if ($result->num_rows == 0) {
			showError("Your search terms did not yield any results!");
		} else {
?>
        <h3><?php print $ListHeading; ?></h3>
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
if ($configOptions['enable_pagination'] == "true") {
    $NextLink = '<a href="'. $_SERVER['PHP_SELF'] .'?mode=' . $Mode .'&param=' . $_GET['param'] .'&offset='.($offset+$LimitPerPage).'">Next &gt;&gt;</a>';
    $PrevLink = '<a href="'. $_SERVER['PHP_SELF'] .'?mode=' . $Mode .'&param=' . $_GET['param'] .'&offset='.($offset-$LimitPerPage).'">&lt;&lt; Prev</a>';
    if ($offset > 0) echo $PrevLink . "&nbsp;&nbsp;&nbsp;&nbsp;";
    if (($offset+$LimitPerPage) < countTorrents($Mode, $_GET['param'])) echo $NextLink;
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