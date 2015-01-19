<?php

$pageTitle = "My Profile";
require_once 'header.php';
require_once 'PasswordHash.php';

// Pull the details from the Session
$User = $_SESSION['user'];
$CurrentPassword = $_SESSION['pass'];
$Fullname = $_SESSION['fullname'];
$AccountType = $_SESSION['acct_type'];

// Add our script
 echo <<<_END
  <script type="text/javascript">
    function checkNewPass()
    {
      pass1 = O('newpass').value
      pass2 = O('newpass2').value
      if (pass1 == pass2) {
        O('info_newpass').innerHTML = "<span class='available'>&nbsp;&#x2714; The passwords match.</span>"
      } else {
        O('info_newpass').innerHTML = "<span class='taken'>&nbsp;&#x2718; The passwords don't match!</span>"
      }
      
    }
    function changePass()
    {
        user = O('user').innerHTML
        pass1 = O('newpass').value
        pass2 = O('newpass2').value
        if (pass1 == pass2) {
          // reset the info to nothing on oldpass 
          O('info_oldpass').innerHTML = ''

          // get the pass and user here and pass it off 
          params  = "user=" + user + "&pass=" + pass1
          request = new ajaxRequest()
          request.open("POST", "changepass.php", true)
          request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
          //request.setRequestHeader("Content-length", params.length)
          //request.setRequestHeader("Connection", "close")

          request.onreadystatechange = function()
          {
            if (this.readyState == 4)
              if (this.status == 200)
                if (this.responseText != null)
                  O('info_newpass').innerHTML = this.responseText
          }
          request.send(params) 
        }
    }
    function changeName()
    {
      user = O('user').innerHTML
      fullname = O('newfullname').value
      if (fullname == '')
      {
        O('info_fullname').innerHTML = ''
        return
      }

      params  = "user=" + user + "&fullname=" + fullname
      request = new ajaxRequest()
      request.open("POST", "changename.php", true)
      request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
      //request.setRequestHeader("Content-length", params.length)
      //request.setRequestHeader("Connection", "close")

      request.onreadystatechange = function()
      {
        if (this.readyState == 4)
          if (this.status == 200)
            if (this.responseText != null)
              O('info_fullname').innerHTML = this.responseText
      }
      request.send(params)      
    }
    function checkOldPass()
    {
      user = O('user').innerHTML;
      pass = O('oldpass').value
      if (pass == '')
      {
        O('info_oldpass').innerHTML = ''
        return
      }

      params  = "user=" + user + "&pass=" + pass
      request = new ajaxRequest()
      request.open("POST", "checkpass.php", true)
      request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
      //request.setRequestHeader("Content-length", params.length)
      //request.setRequestHeader("Connection", "close")

      request.onreadystatechange = function()
      {
        if (this.readyState == 4)
          if (this.status == 200)
            if (this.responseText != null)
              O('info_oldpass').innerHTML = this.responseText
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

/* Old Non-Dynamic Code
if (isset($_POST['mode'])) {
	$Mode = $_POST['mode'];
	if ($Mode == "change-fullname") {
		
		//mySQL query to change the fullname
		$newfullname = $_POST['newfullname'];
		$user = $_SESSION['user'];
		$queryString = "UPDATE members SET fullname='$newfullname' WHERE user='$user';";	
		$result = queryMySQL($queryString);
        if (!$result) {
			echo "<h3>Ooops!!!</h3><br>There was an error changing your Display Name!";	
		} else {
			echo "<h3>Success!</h3><br>Your Display Name was successfully changed!";	
			$_SESSION['fullname'] = $newfullname;
		}
	} elseif ($Mode == "change-passwd") {
		$OldPasswd = $_POST['oldpass'];
		$NewPasswd = $_POST['newpass'];
		$NewPasswd2 = $_POST['newpass2'];
		
		// Check old password for validity
		if ($OldPasswd != $_SESSION['pass']) {
			echo "<h3>Ooops!!!</h3><br>You incorrectly entered your Old Password! <a href='profile'>Try again</a>!";
		} else {
			// then check two new passwords against each other
			if ($NewPasswd != $NewPasswd2) {
				echo "<h3>Ooops!!!</h3><br>Your New Password entries <b>DO NOT</b> match! <a href='profile'>Try again</a>!";
			} else {
				// Update mySQL	
				$PasswordHashed = PasswordHash::create_hash($NewPasswd);
				$queryString = "UPDATE members SET pass='$PasswordHashed' WHERE user='$user';";	
				$result = queryMySQL($queryString);
				if (!$result) {
					echo "<h3>Ooops!!!</h3><br>There was an error changing your Password!";	
				} else {
					echo "<h3>Success!</h3><br>Your Password was successfully changed!";	
					$_SESSION['pass'] = $NewPasswd;
				}
			}
		}
	}
} else { */

?>
<!-- here's the actual page -->
        <table width="650px">
        <tr>
        	<td class="rowcap" width="168px">User Name:</td>
            <td class="rowdata"><strong><span id="user"><?php print $User; ?></span></strong></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Account Type:</td>
            <td class="rowdata"><strong><?php print $AccountTypes[$AccountType]; ?></strong></td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Display Name:</td>
            <td class="rowdata">
			<!--<form method="post" action="#">-->
            <input type="hidden" name="mode" value="change-fullname">
            <table>
            <tr>
            	<td><input type="text" maxlength="72" required="required" id="newfullname" name="newfullname" value="<?php print $Fullname; ?>"></td>
                <td>&nbsp;&nbsp;&nbsp;</td>
                <td><input type="submit" onclick="changeName()" value="Change..." id="submit">&nbsp;&nbsp;<span id='info_fullname'></span></td>
            </tr>
            </table>			
            <!--</form>-->
            </td>
        </tr>
        <tr>
        	<td class="rowcap" width="168px">Password:</td>
            <td class="rowdata">
			<!--<form method="post" action="profile">-->
            <input type="hidden" name="mode" value="change-passwd">
            <br>
            <table>
            <tr>
            	<td align="right">Old Password:</td>
                <td><input type="password" maxlength="72" name="oldpass" id="oldpass" required="required" placeholder="Old Password" onblur="checkOldPass()"></td>
            </tr>
			<tr>
            	<td align="right">New Password:</td>
                <td><input type="password" maxlength="72" name="newpass" id="newpass" required="required" placeholder="New Password"></td>
            </tr>
            <tr>
            	<td align="right">Re-type:</td>
                <td><input type="password" maxlength="72" name="newpass2" id="newpass2" required="required" placeholder="Confirm New Password" onblur="checkNewPass()"></td>
            </tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr>
            	<td>&nbsp;</td>
                <td align="right">
                    <span id='info_oldpass'></span><br>
                    <span id='info_newpass'></span>
                    <br><br>
                    <input type="submit" value="Change..." id="submit" onclick="changePass()">
                </td>
            </tr>
            </table>
           	<!--</form>-->
            <br>
            </td>
        </tr>
        </table>
<!-- end actual page -->
<?php
/* End of Old Dynamic Code 
} */

require_once 'footer.php';

?>