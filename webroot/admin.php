<?php
	// Put out the header
	$pageTitle = "Admin";
	require_once(dirname(__FILE__) . '/include/pieces/header.php');

	$success = "";

	// Add our script
 	echo <<<_END
  <script type="text/javascript">
  function statusModified(user)
  {
  	infoID  = "info_" + user
  	O(infoID).innerHTML = "<span class='taken'>&nbsp;&#x2718; Modified</span>";
  }
  function removeUser(user)
  {
  	  // lets make sure they really want to do this
  	if (!confirm('Are you sure you want PERMANENTLY REMOVE this account?')) {
  		return
	}

      params  = "user=" + user
      request = new ajaxRequest()
      request.open("POST", "post/deluser.php", true)
      request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
      //request.setRequestHeader("Content-length", params.length)
      //request.setRequestHeader("Connection", "close")

      request.onreadystatechange = function()
      {
        if (this.readyState == 4)
          if (this.status == 200)
            if (this.responseText != null) {
              if (this.responseText == 'success') 
              	window.location = 'admin'
              else
              	O('info_' + user).innerHTML = this.responseText
            }
      }
      request.send(params)
  }
  function saveChanges(user)
  {
  	e = O(user + "_acct_type")
  	acct_type = e.options[e.selectedIndex].value
  	fullname = O(user + "_fullname").value
  	c = O(user + "_certified")
  	certified = c.options[c.selectedIndex].value
  	login = O(user + "_login").value

      params  = "user=" + user + "&acct_type=" + acct_type + "&fullname=" + fullname + "&certified=" + certified + "&login=" + login
      request = new ajaxRequest()
      request.open("POST", "post/moduser.php", true)
      request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
      //request.setRequestHeader("Content-length", params.length)
      //request.setRequestHeader("Connection", "close")

      request.onreadystatechange = function()
      {
        if (this.readyState == 4)
          if (this.status == 200)
            if (this.responseText != null)
              O('info_' + user).innerHTML = this.responseText
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

	// SECURITY: If we are not logged in, you shouldn't be admin
	if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

	// SECURITY: If we are not an Administrator, we shouldn't be on the Admin Panel
	if ($_SESSION['acct_type'] != ACCT_TYPE_ADMIN) echo '<script type="text/javascript">window.location = "/"</script>';

	// Check to see if we are in a POST mode, or if we need to output the actual page for starters
	if (isset($_POST['admin_mode'])) {
		// process here
		switch($_POST['admin_mode']) {
			case "save_config":
				$result = queryMySQL("select * from options");
				if ($result->num_rows != 0) {
					while($row = $result->fetch_object()) { 
						$opn_name = $row->name;
						$new_value = EscapeQuotes($_POST["$opn_name"]);
						queryMySQL("UPDATE options SET value='$new_value' WHERE name='$opn_name';");
					}
				}			
        // Load the new options back from MySQL into their arrays
        loadOptionsfromMySQL();

        // Output the success
				$success = "<span class='available'>&nbsp;&#x2714; Settings saved!</span>";
				break;
      case "load_defaults":
        $result = queryMySQL("select * from options");
        if ($result->num_rows != 0) {
          while($row = $result->fetch_object()) { 
            $opn_name = $row->name;
            $new_value = EscapeQuotes($row->default);
            queryMySQL("UPDATE options SET value='$new_value' WHERE name='$opn_name';");
          }
        }     

        // Output the success
        $success = "<span class='available'>&nbsp;&#x2714; Loaded defaults!</span>";
        break;
			default:
				// do nothing as we don't have this mode
        break;
		}
	} 
?>
<!-- Actual Admin Panel Page -->
<h3>Feature Options</h3>
<form id="defaults_form" method="post" action="admin">
<input type='hidden' name='admin_mode' value='load_defaults'>
</form>
<form method='post' action='admin'>
<input type='hidden' name='admin_mode' value='save_config'>
<table width="650px">
<tr>
    <td class="rowcap" width="500px" style="text-align:left;">Name:</td>
    <td class="rowcap" style="text-align:center;">Setting:</td>
</tr>
<?php 
	$result = queryMySQL("SELECT * FROM options WHERE type='bool' OR type='int' ORDER BY description;");
	if ($result->num_rows != 0) {
		while($row = $result->fetch_object()) { 
			$opn_name = $row->name;
			$opn_desc = $row->description;
			$opn_value = $row->value;
      $opn_type = $row->type;
?>
<tr>
	<td class="rowdata" style="text-align:left;"><?php print $opn_desc; ?></td>
	<td class="rowdata" style="text-align:center;">
    <?php 
      switch ($opn_type) {
        case "bool":
    ?>
      		<select class="select-style" name="<?php print $opn_name; ?>">
      			<option value="true"<?php if ($opn_value == "true") echo ' selected="selected"'; ?>>On</option>
      			<option value="false"<?php if ($opn_value == "false") echo ' selected="selected"'; ?>>Off</option>
      		</select>
    <?php 
          break;
        case "int":
    ?>
          <input type="number" min="1" max="255" step="1" value="<?php print $opn_value; ?>" name="<?php print $opn_name; ?>">
    <?php 
        break;
      } // end switch statement
    ?>
	</td>
</tr>
<?php 
		}
	}
?>
</table>
<h3>Strings Editor</h3>
<table width="75%">
<tr>
    <td class="rowcap" width="30%" style="text-align:left;">Name:</td>
    <td class="rowcap" style="text-align:center;">Setting:</td>
</tr>
<?php 
  $result = queryMySQL("SELECT * FROM options WHERE type='string' ORDER BY description;");
  if ($result->num_rows != 0) {
    while($row = $result->fetch_object()) { 
      $opn_name = $row->name;
      $opn_desc = $row->description;
      $opn_value = $row->value;
?>
<tr>
  <td class="rowdata" style="text-align:left;"><?php print $opn_desc; ?></td>
  <td class="rowdata" style="text-align:center;">
    <input type="text" style="width:98%;" maxlength="255" id="<?php print $opn_name; ?>" name="<?php print $opn_name; ?>" value="<?php print $opn_value; ?>"
  </td>
</tr>
<?php 
    }
  }
?>
</table>
<br><br>
<?php print $success; ?>
<br><br>
<input type='submit' value='Save' id='submit'>&nbsp;&nbsp;<input type='submit' value='Defaults' id='submit' form='defaults_form'>
</form>
<br><br>
<h3>User Administration</h3>
<table width="90%" class="sortable">
<tr>
    <td class="rowcap" style="text-align:center;">Avatar:</td>
    <td class="rowcap" style="text-align:center;">Type:</td>
    <td class="rowcap" style="text-align:center;">Login:</td>
    <td class="rowcap" style="text-align:center;">Display Name:</td>
    <td class="rowcap" style="text-align:center;">Certified:</td>
    <td class="rowcap" style="text-align:left;" width="40%">Actions:</td>
</tr>
<?php

	// this is where we go through all the stuff and come up with the user list and available actions
	$result = queryMySQL("SELECT user,pass,fullname,acct_type FROM members");
	if ($result) {
		// process all of the users
		while($row = $result->fetch_object()) { 
			$user = $row->user;
			$fullname = $row->fullname;
			$acct_type = $row->acct_type;

			// make sure not to show our own user cuz yea
			if ($user != $_SESSION['user']) {
?>
<!-- rows for accounts -->
<tr>
    <td class="rowdata" style="text-align:center;">
      <?php
        $filename = "avatars/" . $user  . ".jpg";
        $have_avatar = FALSE;
        if (file_exists($filename)) {
          // echo the image tag for the actual avatar
          //echo "<a href='#avatar_fullsize_$user' rel='ibox&width=150&height=150' title='Avatar'>";
          echo "<img src='$filename' width='50' height='50' ALT='Avatar Mini'>";
          //echo "</a>";
          //echo "<div id='avatar_fullsize_$user' style='display:none;'><img src='$filename' width='100' height='100' ALT='Avatar'></div>";
          $have_avatar = TRUE;
        } else {
          // the avatar doesn't exist, so use the default
          //echo "<a href='#avatar_fullsize_$user' rel='ibox&width=150&height=150' title='Avatar'>";
          echo "<img src='img/default_avatar.jpg' width='50' height='50' ALT='Avatar Mini'>";
          //echo "</a>";
          //echo "<div id='avatar_fullsize_$user' style='display:none;'><img src='img/default_avatar.jpg' width='100' height='100' ALT='Avatar'></div>";
          $have_avatar = FALSE;
        }
      ?> 
    </td>
    <td class="rowdata" style="text-align:center;">
    	<select class="select-style" id="<?php print $user; ?>_acct_type" onchange="statusModified('<?php print $user; ?>')">
		    <?php 
		    	foreach ($AccountTypes as $key => $value) {
		    		echo "<option value='$key'";
		    		if ($key == $acct_type) echo " selected='selected'";
		    		echo ">$value</option>";
		    	}
		    	//print $AccountTypes[$acct_type]; 
		    ?>
		</select>
    </td>
    <td class="rowdata" style="text-align:center;">
    <input type="text" maxlength="72" id="<?php print $user; ?>_login" name="<?php print $user; ?>_login" value="<?php print $user; ?>" onchange="statusModified('<?php print $user; ?>')" onkeypress="statusModified('<?php print $user; ?>')">
    </td>
    <td class="rowdata" style="text-align:center;">
    <input type="text" maxlength="72" id="<?php print $user; ?>_fullname" name="<?php print $user; ?>_fullname" value="<?php print $fullname; ?>" onchange="statusModified('<?php print $user; ?>')" onkeypress="statusModified('<?php print $user; ?>')">
    </td>
    <td class="rowdata" style="text-align:center;">
    	<select class="select-style" id="<?php print $user; ?>_certified" onchange="statusModified('<?php print $user; ?>')">
    		<?php print isCertified($user,"radio"); ?>
    	</select>
    </td>
    <td class="rowdata" style="text-align:left;">
    	<input type='submit' value='Save' id='submit_small' onclick="saveChanges('<?php print $user; ?>')">
    	<input type='submit' value='Remove' id='submit_small' onclick="removeUser('<?php print $user; ?>')">
    	&nbsp;&nbsp;&nbsp;<span id='info_<?php print $user; ?>'></span>
    </td>
</tr>
<!-- end account rows -->
<?php 		
			}	
		}
	}


?>
</table>
<Br><Br>
<!-- End page -->
<?php

	// Put out the footer
	require_once(dirname(__FILE__) . '/include/pieces/footer.php');
?>
