<?php
	// Put out the header
	$pageTitle = "Author";
	require_once(dirname(__FILE__) . '/include/pieces/header.php');
	
	// SECURITY: If we are not logged in, you shouldn't be here
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

	// Grab all of the information we need in order to show details on the Author
	$Author = $_GET['name'];

	// Get the Display Name of the Author (UGLY HACK cuz our other function checks the anonymous thingy)
	$result = queryMysql("SELECT fullname,acct_type FROM members WHERE user='$Author';");
	$row = $result->fetch_object();
	$DisplayName = $row->fullname;
	$AccountType = $row->acct_type;

	// Check to see if the Avatar exists, if not use the default
	$AvatarPath = "avatars/" . $Author . ".jpg";
	if (!file_exists($AvatarPath)) $AvatarPath = "img/default_avatar.jpg";

	// Now we are going to count Torrents, Files, and Total Size
	$TotalTorrents = 0;
	$TotalFileCount = 0;
	$TotalSize = 0;

	$result = queryMysql("SELECT * from torrents WHERE author='$Author';");
	if ($result->num_rows != 0) {
		while($row = $result->fetch_object()) { 
			$TotalTorrents += 1;
			$TotalFileCount += $row->filecount;
			$TotalSize += $row->size;
		}
	}

	$TotalHumanSize = humanFileSize($TotalSize);
?>
<!-- Start Author Page -->
<table width="500px">
	<tr>
		<td class="rowcap_author" colspan="2">
			<table align="center">
				<tr>
					<td><img src="<?php print $AvatarPath; ?>" height="100" width="100" ALT="Avatar"></td>
					<td>&nbsp;</td>
					<td><?php print $DisplayName; ?></td>
					<td>&nbsp;</td>
					<td>
						<?php
							if (isCertified($Author,"bool"))
								echo "<img src='/img/skull-icon.png' height='24' width='24' alt='Certified Uploader' />";
							else
								echo "&nbsp;";
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="sidecol_author" width="200px">Status:</td>
		<td class="rowdata">
			<?php print $AccountTypes[$AccountType]; ?>
		</td>
	</tr>
	<tr>
		<td class="sidecol_author" width="200px">Certified:</td>
		<td class="rowdata">
			<?php
				if (isCertified($Author,"bool"))
					echo "Yes";
				else 
					echo "No";
			?>
		</td>
	</tr>
	<tr>
		<td class="sidecol_author" width="200px">Uploaded:</td>
		<td class="rowdata">
			<?php 
				if ($TotalTorrents != 0)
					print "<a href='listby?mode=author&param=" . $Author . "'>" . $TotalTorrents . " torrents (" . $TotalHumanSize . " in " . $TotalFileCount . " files)</a>";
				else
					print "0 torrents";
			?> 
		</td>
	</tr>
</table>
<!-- End Author Page -->
<?php
	// Footer
	require_once(dirname(__FILE__) . '/include/pieces/footer.php');
?>
