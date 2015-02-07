<?php
    $pageTitle = "Torrent Details";
	require_once 'header.php';
    require_once 'Parsedown.php';

    // Init the MarkDown parsing library
    $Parsedown = new Parsedown();
    
    // Add our script
    echo <<<_END
  <script type="text/javascript">
    function toggleEditMode()
    {
       btnvalue = O('toggle').value
       if (btnvalue == 'Edit') {
            // we are editing, so we need to switch all the items over to the form items
            // and change the value of the button to save.

            // Hide the original fields 
            O('name_holder').style.display = 'none'
            O('type_holder').style.display = 'none'
            O('desc_holder').style.display = 'none'


            // Make the editor fields visible
            O('new_name').style.display = 'inline'
            O('type_chooser').style.display = 'inline'
            O('new_desc').style.display = 'inline'
            O('save').style.display = 'inline'

            // Change the name/mode of the button
            O('toggle').value = 'Cancel'
       } else {
            O('toggle').value = 'Edit'

            // Show the original fields 
            O('name_holder').style.display = 'inline'
            O('type_holder').style.display = 'inline'
            O('desc_holder').style.display = 'inline'


            // Hide the editor fields
            O('new_name').style.display = 'none'
            O('type_chooser').style.display = 'none'
            O('new_desc').style.display = 'none'
            O('save').style.display = 'none'
       }
    }
    function loadTemplate()
    {
      // get type of torrent we are trying to upload from option box 
      e = O("torrent-type")
      type = e.options[e.selectedIndex].text
          
      // get the pass and user here and pass it off 
      params  = "type=" + type
      request = new ajaxRequest()
      request.open("POST", "desc.php", true)
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

	if ($loggedin) {
		
		// First let's get the torrent's info-hash from the GET protocol
		$TorrentHash = $_GET['hash'];

		// Now it is time to make the database connection and find all of our information
		$result = queryMySQL("select * from torrents where hash ='". $TorrentHash ."'");
	
		$row = $result->fetch_object();
		$TorrentName = $row->name;
		$TorrentType = $TorrentTypes[$row->type];
		$TorrentUploaded = date("Y-m-d @ h:ia",$row->uploaded);
		$TorrentFiles = $row->files;
		$TorrentComment = $row->comment;
		$TorrentMagnet = $row->magnet;
		$TorrentFile = "uploads/" . $TorrentHash . ".torrent";
		$TorrentAuthor = getDisplayName($row->author);
		$TorrentSize = $row->size;
		$TorrentFileCount = $row->filecount;

        // Do the description parsing (take MarkDown text from database and change into HTML to display)
        $DescMarkDown = $row->description;
        $TorrentDesc = $Parsedown->text($row->description);
?>
    	<!-- Torrent Information -->
        <form action="edit" method="post" id="edit_form">
        </form>
        <table width="992px">
        <tr>
        	<td class="rowcap" width="168px">Name:</td>
            <td class="rowdata" style="font-family:Aclonica; font-size:20px;">
            <input type="hidden" name="info_hash" id="info_hash" form="edit_form" value="<?php print $TorrentHash; ?>">
            <strong>
            <span id="name_holder"><?php print $TorrentName; ?></span>
            <input form='edit_form' type='text' autofocus='autofocus' style='width:98%;display:none;' maxlength='256' id='new_name' name='new_name' value='<?php print $TorrentName; ?>'>
            </strong></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Comment:</td>
            <td class="rowdata" style="font-family:Snippet;"><em><?php print $TorrentComment; ?></em></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Type:</td>
            <td class="rowdata">
            <div id="type_chooser" style="display:none;">
            <select form="edit_form" class="select-style" name="torrent-type" id="torrent-type" width="200px">
            <?php
                foreach ($TorrentTypes as $key => $value) {
                    echo "<option value='$key'";
                    if ($TorrentType == $value) echo " selected='selected'";
                    echo ">$value</option>";
                }
            ?>
            </select>
            </div>
            <span id="type_holder">
                <table>
                    <tr>
                    <td>
                        <img src="img/type_icons/<?php print $TorrentType; ?>.png" ALT="<?php print $TorrentType; ?>" width="32px" height="32px">
                    </td>
                    <td>&nbsp;</td>
                    <td>
                        <?php print $TorrentType; ?>
                    </td>
                    </tr>
                </table>                
            </span></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Author:</td>
            <td class="rowdata">
            	<table>
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
                    <td><img src="/img/files-icon.png" height="24" width="24" alt="Files" /></a></td>
                    <td>&nbsp;</td>
                    <td><a href="#file_list" rel="ibox&width=900&height=500" title="Torrent's Files" >Show List of Files...</a></td>
                </tr>
            </table>
            </td>
        </tr>
        <tr>
        	<td class="rowcap">Description:</td>
            <td class="rowdata">
                <div class="torrent-desc">
                <span id="desc_holder"><?php print $TorrentDesc; ?></span>
                </div>
                <textarea form='edit_form' style='display:none;' id='new_desc' name='new_desc' rows='20' cols='100' placeholder='Brief description of your torrent... Or to start with a Description Template, click the button above! MarkDown syntax is supported, see link above for help.' required='required'><?php print $DescMarkDown; ?></textarea>
            </td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Download:</td>
            <td class="rowdata">
            	<table>
                	<tr>
                    	<td><a href="<?php print $TorrentMagnet; ?>"><img src="/img/Magnet.png" height="32" width="32" alt="MAGNET" /></a></td>
                        <td>&nbsp;&nbsp;<a href="<?php print $TorrentMagnet; ?>">Magnet</a>&nbsp;</td>
                        <?php if ($configOptions_Booleans['hide_torrent_files'] == "false") { ?>
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
	if (($row->author == $_SESSION['user'] && $configOptions_Booleans['admin_only_removes'] == "false") || $_SESSION['acct_type'] == ACCT_TYPE_ADMIN) {
		// This torrent belongs to the user logged in. Add option to remove/edit it.
?>
<!-- only shown if the user logged in happens to be the owner of the torrent we are showing details on -->
        <tr>
        	<td class="rowcap" width="168px">Actions:</td>
            <td class="rowdata">
                <table>
                    <tr>
                        <td>
                            <form method="post" action="remove" onsubmit="return confirm('Are you sure you want to permanently REMOVE this torrent?');">
                                <input type="hidden" name="torrent-hash" value="<?php print $TorrentHash; ?>" />
                                <input type="submit" value="Remove" id="submit">
                            </form>    
                        </td>
                        <td>&nbsp;&nbsp;&nbsp;</td>
                        <td><input type="submit" value="Edit" id="toggle" onclick="toggleEditMode()"></td>
                        <td>&nbsp;</td>
                        <td><input form='edit_form' style='display:none;' type="submit" value="Save" id="save"></td>
                    </tr>
                </table>
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