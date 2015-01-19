<?php
    $pageTitle = "Torrent Details";
	require_once 'header.php';
    
	if ($loggedin) {
		
		/* First let's get the torrent's info-hash from the GET protocol */
		if (!isset($_GET['hash'])) die('<h3>Ooops!!!</h3><br><b><u>Error:</b></u> <i>Invalid Info-Hash!<i>');
		$TorrentHash = $_GET['hash'];

		/* We need to make sure that info hash isn't empty */
		if ($TorrentHash === "") die('<h3>Ooops!!!</h3><br><b><u>Error:</b></u> <i>Invalid Info-Hash!<i>');

		/* Now it is time to make the database connection and find all of our information */
		$result = queryMySQL("select * from torrents where hash ='". $TorrentHash ."'") or die('<h3>Ooops!!!</h3><br><b><u>Error:</b></u> <i>Info-Hash not in database!<i>');
		
		// Check to make sure we have it in the database before continuing
		if ($result->num_rows == 0) {
        	die('<h3>Ooops!!!</h3><br><b><u>Error:</b></u> <i>Info-Hash not in database!<i>');
        }
	
		$row = $result->fetch_object();
		$TorrentName = $row->name;
		$TorrentType = $row->type;
		$TorrentUploaded = date("Y-m-d @ h:ia",$row->uploaded);
		$TorrentFiles = $row->files;
		$TorrentComment = $row->comment;
		$TorrentDesc = $row->description;
		$TorrentMagnet = $row->magnet;
		$TorrentFile = "uploads/" . $TorrentHash . ".torrent";
		$TorrentAuthor = getDisplayName($row->author);
		$TorrentSize = $row->size;
		$TorrentFileCount = $row->filecount;
?>
    	<!-- Torrent Information -->
        <table width="992px">
        <tr>
        	<td class="rowcap" width="168px">Name:</td>
            <td class="rowdata" style="font-family:Aclonica; font-size:20px;"><strong><?php print $TorrentName; ?></strong></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Comment:</td>
            <td class="rowdata" style="font-family:Snippet;"><em><?php print $TorrentComment; ?></em></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Type:</td>
            <td class="rowdata"><?php print $TorrentType; ?></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Author:</td>
            <td class="rowdata">
            	<table>
                	<tr>
                        <td><?php print $TorrentAuthor; ?></td>
                        <td><?php print isCertified($row->author); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Uploaded:</td>
            <td class="rowdata"> <?php print $TorrentUploaded; ?></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Size:</td>
            <td class="rowdata"> <?php print humanFileSize($TorrentSize) . " in " . $TorrentFileCount . " files"; ?></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Files:</td>
            <td class="rowdata">
            <div id="file_list" style="display:none;">
            <blockquote>
            <?php print $TorrentFiles; ?>
            </blockquote>
            </div>
            <table>
                <tr>
                    <td><img src="/img/files-icon.png" height="13" width="16" alt="Files" /></a></td>
                    <td><a href="#file_list" rel="ibox&width=900&height=500" title="Torrent's Files" >Show List of Files...</a></td>
                </tr>
            </table>
            </td>
        </tr>
        <tr>
        	<td class="rowcap">Description:</td>
            <td class="rowdata">
            <pre>
<?php print $TorrentDesc; ?>
            </pre>
            </td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Download:</td>
            <td class="rowdata">
            	<table>
                	<tr>
                    	<td><a href="<?php print $TorrentMagnet; ?>"><img src="/img/Magnet.png" height="32" width="32" alt="MAGNET" /></a></td>
                        <td>&nbsp;&nbsp;<a href="<?php print $TorrentMagnet; ?>">Magnet</a>&nbsp;</td>
                        <?php if ($configOptions['hide_torrent_files'] == "false") { ?>
                        <!-- only displayed if we want to show torrent files -->
                        <td><a href="<?php print $TorrentFile; ?>"><img src="/img/Download.png" height="32" width="32" alt="TORRENT" /></a></td>
                        <td>&nbsp;&nbsp;&nbsp;<a href="<?php print $TorrentFile; ?>">.Torrent</a></td>
                        <!-- end display torrent file -->
                        <?php } ?>
                    </tr>
            	</table>
            </td>
        </tr>
<?php
	if (($row->author == $_SESSION['user'] && $configOptions['admin_only_removes'] == "false") || $_SESSION['acct_type'] == ACCT_TYPE_ADMIN) {
		// This torrent belogns to the user logged in. Add option to remove it. TODO: Add option to change the description and torrent type.
?>
<!-- only shown if the user logged in happens to be the owner of the torrent we are showing details on -->
        <tr>
        	<td class="rowcap" width="168px">Remove:</td>
            <td class="rowdata">
				<form method="post" action="remove">
                	<input type="hidden" name="torrent-hash" value="<?php print $TorrentHash; ?>" />
					<input type="submit" value="Remove" id="submit">&nbsp;&nbsp;&nbsp;(Note: This will <b><u>ONLY</b></u> remove the torrent from our databases!)
                </form>
            </td>
        </tr>
<!-- end remove torrent stuff -->
<?php
	}
?>
        </table>
	</td>
  </tr>
<?php
	}
	else {
		//echo '<h3>Access Denied!</h3><br>You are not currently <strong>LOGGED IN</strong>. Please login above.';
		$HASH = $_GET['hash'];
		echo '<script type="text/javascript">window.location = "/login?page=details?hash=' . $HASH .'"</script>';
	}

    require_once 'footer.php';
?>