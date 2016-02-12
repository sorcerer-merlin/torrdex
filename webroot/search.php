<?php
	$pageTitle = "Search";
	require_once(dirname(__FILE__) . '/include/pieces/header.php');
    
	// SECURITY: If we are not logged in, you shouldn't be uploading
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "login?page=search"</script>';

    // Java script
    echo <<<_END
  <script type="text/javascript">
    function doSortSwitch(newcolumn)
    {
        // Get all of the values 
        orderby = O('orderby').value
        sortorder = O('sortorder').value
        keywords = O('keywords').value
        type = O('type').value
        offset = O('offset').value

        // switch the sort order, first checking to see if we have a new column to switch to, if so
        // default sort order is desc
        if (orderby == newcolumn) { 
            // no column change
            if (sortorder == 'desc')
                sortorder = 'asc'
            else
                sortorder = 'desc'
            offset = '0'
        } else {
            // we have a new column so start with default sort order DESC and reset offset to 0
            sortorder = 'asc'
            offset = '0'
        }


        // Fix new values
        O('orderby').value = newcolumn
        O('sortorder').value = sortorder
        O('offset').value = offset

        // Change the page based on what we got 
        window.location = "search?keywords=" + keywords + "&type=" + type + "&offset=" + offset + "&orderby=" + newcolumn + "&sortorder=" + sortorder
    }
  </script>
_END;

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
	
    $Keywords = $_GET['keywords'];
    $Type = $_GET['type'];

    // Pagination. Check to see if they specified a start page number, or not. Fix it.
    $LimitPerPage = $configOptions_Integers['torr_per_page'];
    if (!isset($_GET['offset']) or !is_numeric($_GET['offset'])) {
      //we give the value of the starting row to 0 because nothing was found in URL
      $offset = 0;
    //otherwise we take the value from the URL
    } else {
      $offset = (int)$_GET['offset'];
    }

    if ($configOptions_Booleans['enable_pagination'] == "true") {
        $Paging = " LIMIT " . $offset . "," . $LimitPerPage;
        // Let's do an order by parameter from the GET var
        if (!isset($_GET['orderby'])) {
            $OrderBy = 'uploaded';
        } else {
            if (!isValidSortColumn($_GET['orderby'])) 
                $OrderBy = 'uploaded';
            else
                $OrderBy = $_GET['orderby'];
        }
        // Let's do a sorting order parameter from the GET var
        if (!isset($_GET['sortorder'])) {
            $SortOrder = 'desc';
        } else {
            if (!isValidSortOrder($_GET['sortorder']))
                $SortOrder = 'desc';
            else
                $SortOrder = $_GET['sortorder'];
        }
    } else {
        $Paging = "";
        $OrderBy = 'uploaded';
        $SortOrder = 'desc';
    }

	// Do the search from the database
    // UPDATE: We added type searches now, so check the type being submitted and then go from there
    $SearchHeading = "";
    if ($_GET['type'] == -1) {
        $finalQuery = "SELECT * FROM torrents WHERE  name LIKE '%" . $_GET['keywords'] . "%' ORDER BY " . $OrderBy . " " . strtoupper($SortOrder) . $Paging . ";";
        $SearchHeading = "Search Results";
    } else {
        $finalQuery = "SELECT * FROM torrents WHERE  name LIKE '%" . $_GET['keywords'] . "%' AND type='" . $_GET['type'] . "' ORDER BY " . $OrderBy . " " . strtoupper($SortOrder) . $Paging . ";";
        $SearchHeading = "Search Results in " . $TorrentTypes[$_GET['type']];
    }
    //echo $finalQuery . "<br>";
	$result = queryMySQL($finalQuery);

	// Check to make sure we have it in the database before continuing
	if ($result->num_rows == 0) {
		showError("Your search terms did not yield any results! <a href='search'>Try again</a>?");
	} else {
?>
        <h3><?php print $SearchHeading ?></h3>
        <?php
            echo <<<_ENDD
            <input type="hidden" id="keywords" value="$Keywords">
            <input type="hidden" id="type" value="$Type">
            <input type="hidden" id="offset" value="$offset">
            <input type="hidden" id="orderby" value="$OrderBy">
            <input type="hidden" id="sortorder" value="$SortOrder">
_ENDD;
        ?>
        <table width="90%"<?php if ($Paging == "") print ' class="sortable"'; else print ' class="jsort"'; ?>>
        <tr>
        	<td class="rowcap"<?php if ($Paging != "") print " onclick=\"doSortSwitch('type')\""; ?>>Type:<?php if ($OrderBy == "type" && $Paging != "") print " " . getSortIcon($SortOrder); ?></td>
            <td class="rowcap" width="40%" style="text-align:center;"<?php if ($Paging != "") print " onclick=\"doSortSwitch('name')\""; ?>>Name:<?php if ($OrderBy == "name" && $Paging != "") print " " . getSortIcon($SortOrder); ?></td>
            <td class="rowcap"<?php if ($Paging != "") print " onclick=\"doSortSwitch('uploaded')\""; ?>>Age:<?php if ($OrderBy == "uploaded" && $Paging != "") print " " . getSortIcon($SortOrder); ?></td>
            <td class="rowcap"<?php if ($Paging != "") print " onclick=\"doSortSwitch('seeders')\""; ?>>Seeds:<?php if ($OrderBy == "seeders" && $Paging != "") print " " . getSortIcon($SortOrder); ?></td>
            <td class="rowcap"<?php if ($Paging != "") print " onclick=\"doSortSwitch('leechers')\""; ?>>Peers:<?php if ($OrderBy == "leechers" && $Paging != "") print " " . getSortIcon($SortOrder); ?></td>
            <td class="rowcap"<?php if ($Paging != "") print " onclick=\"doSortSwitch('size')\""; ?>>Size:<?php if ($OrderBy == "size" && $Paging != "") print " " . getSortIcon($SortOrder); ?></td>
            <td class="rowcap"<?php if ($Paging != "") print " onclick=\"doSortSwitch('filecount')\""; ?>>Files:<?php if ($OrderBy == "filecount" && $Paging != "") print " " . getSortIcon($SortOrder); ?></td>
            <td class="rowcap"<?php if ($Paging != "") print " onclick=\"doSortSwitch('author')\""; ?>>Author:<?php if ($OrderBy == "author" && $Paging != "") print " " . getSortIcon($SortOrder); ?></td>
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
        $Seeders = $row->seeders;
        $Leechers = $row->leechers;
?>

<tr>
        	<td class="rowdata">
				<table align="left">
					<tr>
					<td>
						<img src="/style/<?php print $configOptions_Strings['theme_name']; ?>/img/type_icons/<?php print $TorrentType; ?>.png" ALT="<?php print $TorrentType; ?>" width="16px" height="16px">
					</td>
					<td>&nbsp;</td>
					<td>
						<a href="listby?mode=type&param=<?php print $row->type; ?>"><?php print $TorrentType; ?></a>
					</td>
					</tr>
				</table>        		
        	</td>
            <td class="rowdata" width="300px"><a href="details?hash=<?php print $TorrentHash; ?>"><?php print $TorrentName; ?></a></td>
            <td class="rowdata" style="text-align:right;" sorttable_customkey="<?php print $row->uploaded; ?>"><?php print $TorrentAge; ?></td>
            <td class="rowdata" style="text-align:right;"><span class="seeders_number"><?php print number_format($Seeders); ?></span></td>
            <td class="rowdata" style="text-align:right;"><span class="leechers_number"><?php print number_format($Leechers); ?></span></td>
            <td class="rowdata" style="text-align:center;" sorttable_customkey="<?php print $row->size; ?>"><?php print humanFileSize($TorrentSize); ?></td>
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
    $NextLink = '<a href="'. $_SERVER['PHP_SELF'] .'?keywords=' . $_GET['keywords'] .'&type=' . $_GET['type'] .'&offset='.($offset+$LimitPerPage).'&orderby=' . $OrderBy . '&sortorder=' . $SortOrder . '">Next &gt;&gt;</a>';
    $PrevLink = '<a href="'. $_SERVER['PHP_SELF'] .'?keywords=' . $_GET['keywords'] .'&type=' . $_GET['type'] .'&offset='.($offset-$LimitPerPage).'&orderby=' . $OrderBy . '&sortorder=' . $SortOrder . '">&lt;&lt; Prev</a>';
    
    if ($offset > 0) 
        echo $PrevLink . "&nbsp;&nbsp;&nbsp;&nbsp;";
    else
         echo "<span class='disabledlink'>&lt;&lt; Prev</span>&nbsp;&nbsp;&nbsp;&nbsp;";
    
    if (($offset+$LimitPerPage) < countTorrentsSearch($_GET['type'], $_GET['keywords'])) 
        echo $NextLink;
    else
        echo "<span class='disabledlink'>Next &gt;&gt;</span>";
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
    require_once(dirname(__FILE__) . '/include/pieces/footer.php');
?>
