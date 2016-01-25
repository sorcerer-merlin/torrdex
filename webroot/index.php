<?php
	$pageTitle = "Home";
    	require_once(dirname(__FILE__) . '/include/pieces/header.php');
	
	if ($loggedin) {
		$fullname = $_SESSION['fullname'];
		echo "<h3>Welcome back, $fullname!</h3><br>Thanks for coming back to see us, we appreciate your support! Check out our most recent torrents below.<br><br><br><br>";
		
	// List the torrents in Order by newest to Oldest, limit to the 5 most recent
	$result = queryMySQL("SELECT * FROM torrents ORDER BY uploaded DESC LIMIT 5;");
		
	// Check to make sure we have it in the database before continuing
	if ($result->num_rows == 0) {
		showError("Your database is EMPTY. Please contact your Administrator.");
	} else {
?>
		<h3>Five Most Recent Torrents</h3>
        <table width="90%" class="sortable">
        <tr>
        	<td class="rowcap">Type:</td>
            <td class="rowcap" width="40%" style="text-align:center;">Name:</td>
            <td class="rowcap">Age:</td>
            <td class="rowcap">Seeds:</td>
            <td class="rowcap">Peers:</td>
            <td class="rowcap">Size:</td>
            <td class="rowcap">Files:</td>
            <td class="rowcap">Author:</td>
        </tr>
        
<?php
	
	// Go through each one and print it out
	while($row = $result->fetch_object()) { 
		$TorrentType = $TorrentTypes[$row->type];
		$TorrentName = $row->name;
		//$TorrentUploaded = $row->uploaded;
		$TorrentHash = $row->hash;
		$TorrentAuthor = getDisplayName($row->author);
		$TorrentSize = $row->size;
		$TorrentFileCount = $row->filecount;
		$TorrentAge = dateDiff(time(), intval($row->uploaded), 1);
        $Seeders = $row->seeders;
        $Leechers = $row->leechers;


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
	}
?>
</table>
<br><br>
<?php
	}
?>        
        
        

<?php
	} else {           
		//echo '<h3>Access Denied!</h3><br>You are not currently <strong>LOGGED IN</strong>. Please login above.';
		/*echo '<script type="text/javascript">window.location = "login"</script>'; */
?>

<!-- this is where we put the page that is shown when no one is logged in. News page, me thinks. -->
<h1 id="header">TorrDex</h1>

<!-- end news page or whatever -->

<?php
	}
?>
	</td>
  </tr>
<?php  
    require_once(dirname(__FILE__) . '/include/pieces/footer.php');
?>