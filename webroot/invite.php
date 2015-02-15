<?php
    // Put out the header
    $pageTitle = "Invite New Member";
    require_once(dirname(__FILE__) . '/include/pieces/header.php');

// Output the scripting code for us to check the user, etc. dynamically
    echo <<<_END
  <script type="text/javascript">
    function checkPass()
    {
      pass1 = O('pass').value
      pass2 = O('pass2').value
      if (pass1 == pass2) {
        O('info2').innerHTML = "<span class='available'>&nbsp;&#x2714; The passwords match.</span>"
      } else {
        O('info2').innerHTML = "<span class='taken'>&nbsp;&#x2718; The passwords don't match!</span>"
      }
      
    }
    function checkUser(user)
    {
      if (user.value == '')
      {
        O('info').innerHTML = ''
        return
      }

      params  = "user=" + user.value
      request = new ajaxRequest()
      request.open("POST", "post/checkuser.php", true)
      request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
      //request.setRequestHeader("Content-length", params.length)
      //request.setRequestHeader("Connection", "close")

      request.onreadystatechange = function()
      {
        if (this.readyState == 4)
          if (this.status == 200)
            if (this.responseText != null)
              O('info').innerHTML = this.responseText
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

        if (!isset($_GET['mode'])) {
            showError("Improper parameters were passed! Please contact your Administrator.");
        } else {
            // We have a valid user able to do an invite, now we need to process which mode they
            // are on. So get the variable and do the conditionals.
            $Mode = $_GET['mode'];

            $Verified = true;
            if ($Mode != "do_invite") {
                // We need to check for proper permissions here, since we are not being passed a link from an email...

                // SECURITY: If we are not logged in, you shouldn't be inviting
                if ($loggedin == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

                // Check to see if we are an Administrator and Only admins can send out sign up emails,
                // or we are atleast a Seeder.
                if ($configOptions_Booleans['only_admin_invites'] == "true")
                    if ($_SESSION['acct_type'] != ACCT_TYPE_ADMIN) 
                        $Verified = false;
                else
                    if ($_SESSION['acct_type'] == ACCT_TYPE_LEECHER)
                        $Verified = false;
            }

            // If we need to be verified and aren't, kick us out
            if ($Verified == FALSE) echo '<script type="text/javascript">window.location = "/"</script>';

            // Show the form so we can get the info needed to do the actual invite link
            if ($Mode == "do_form") {
?>
<h3>Enter your Friend's details:</h3>
<form action="invite" method="GET">
<input type="hidden" name="mode" value="form_submitted">
<table> 
    <tr>
      <td class="rowcap">&nbsp;&nbsp;Nickname:&nbsp;&nbsp;</td>
      <td class="rowdata">
        <input type="text" maxlength="255" id="nickname" name="nickname" value="" required="required" placeholder="Nickname">
      </td>
    </tr>     
    <tr>
      <td class="rowcap">&nbsp;&nbsp;Email:&nbsp;&nbsp;</td>
      <td class="rowdata">
        <input type="email" maxlength="255" id="email" name="email" value="" required="required" placeholder="Email Address">
      </td>
    </tr>
</table>
<br><br>
<input type='submit' value='Send Invite' id='submit'>
</form>
<?php
            }

            if ($Mode == "form_submitted") {
                // Check for errors, do we have a non-set email field, or blank email, or email that isn't in our database
                if (!isset($_GET['email'])) echo '<script type="text/javascript">window.location = "/invite?mode=do_form"</script>';
                if ($_GET['email'] == "") echo '<script type="text/javascript">window.location = "/invite?mode=do_form"</script>';

                // Now we need to make the code, get the expiration time, and put all of this in the database
                $InvitedBy = $_SESSION['user'];
                $Nickname = EscapeQuotes($_GET['nickname']);
                $Email = $_GET['email'];

                // Now build a random code
                $Code = substr(hash('sha256', mt_rand()), 0, 50);

                // Get current time + 1 day
                $Expires = strtotime('+1 day');

                // Do the actual query and put everything into the DB
                $result = queryMySQL("INSERT INTO invite VALUES ('$InvitedBy', '$Email', '$Nickname', '$Code', '$Expires');");

                $Link = $configOptions_Strings['site_root'] . "invite?mode=do_invite&nickname=" . $Nickname . "&email=" . $Email . "&code=" . $Code;
            //echo "<a href='" . $Link . "'>Test Invitation</a>";
            echo "<h3>Verification</h3><br>An email has been dispatched to your friend's email address with a verification link needed to complete this process. &nbsp;Please have them check their email.";

            // Email the reset link and instructions to the user, and then output some kind of page saying it has been done and they have an
            // hour before it expires.
            sendEmailInvite($Nickname, $Email, $Link);
            }

            if ($Mode == "do_invite") {
                // Check the variables to make sure they exist, and aren't blank. Then check the code for expiration and validity in DB.
                // try to think of any other reasons we might have issues.

                // Check for no exists variables
                if (!isset($_GET['nickname']) && !isset($_GET['email']) && !isset($_GET['code'])) {
                    showError("Improper parameters were passed! Please contact your Administrator.");
                } else {
                    // Variables exist, grab the variables
                    $Nickname = $_GET['nickname'];
                    $Email = $_GET['email'];
                    $Code = $_GET['code'];

                    // Check for blank variables
                    if ($Nickname == "" || $Email == "" || $Code == "") {
                        showError("Improper parameters were passed! Please contact your Administrator.");
                    } else {
                        // Now we have valid non-NULL vars. Let's check the DB for a match.
                        $result = queryMySQL("SELECT * FROM invite WHERE nickname='$Nickname' AND email='$Email' AND code='$Code';");
                        if ($result->num_rows == 0)
                            showError("Improper parameters were passed! Please contact your Administrator.");
                        else {
                            // Now we have a valid invite request, verified by the DB. Get the expiration time.
                            $row = $result->fetch_object();
                            $TimeFromDB = $row->expires;

                            // Check the current time and make sure our offer hasn't expired yet.
                            $Now = time();
                            if ($Now <= $TimeFromDB) {

                                // Do the CAPTCHA stuff
                                $_SESSION = array();
                                require_once(dirname(__FILE__) . '/include/libs/captcha/simple-php-captcha.php');
                                $_SESSION['captcha'] = simple_php_captcha();
?>
<form method='post' action='verify'>
<input type="hidden" name="mode" value="friend_invite">
<input type="hidden" name="code" value="<?php print $Code; ?>">
<input type="hidden" name="time" value="<?php print $TimeFromDB; ?>">
<input type="hidden" name="nickname" value="<?php print $Nickname; ?>">
    <br><br>
    <table>
        <tr>
            <td class="rowcap">&nbsp;&nbsp;Username:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='text' maxlength='32' name='user' value='' autofocus='autofocus' required="required" placeholder="Username" onBlur='checkUser(this)'></td>
        </tr>
        <tr>
          <td class="rowcap">&nbsp;&nbsp;Email:&nbsp;&nbsp;</td>
          <td class="rowdata">
            <input type="email" maxlength="255" id="email" name="email" value="<?php print $Email; ?>" required="required" placeholder="Email Address">
          </td>
        </tr>
        <tr>
            <td class="rowcap">&nbsp;&nbsp;Password:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='password' maxlength='72' id='pass' name='pass' value='' required="required" placeholder="Password"></td>
        </tr>
        <tr>
            <td class="rowcap">&nbsp;&nbsp;Re-enter Password:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='password' maxlength='72' id='pass2' name='pass2' value='' required="required" placeholder="Re-enter Password" onBlur="checkPass()"></td>
        </tr>
        <tr>
          <td class="rowcap">&nbsp;&nbsp;Display Name:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='text' maxlength='72' name='fullname' value='<?php print $Nickname; ?>' autofocus='autofocus' required="required" placeholder="Display Name"></td>
        </tr>
        <tr>
            <td class="rowcap">Security:</td>
            <td class="rowdata">
                <br>
                <table>
                    <tr>
                    <td align="right"><img src="<?php print $_SESSION['captcha']['image_src']; ?>" alt="CAPTCHA"></td>
                    </tr>
                    <tr>
                    <td>
                        <input type="text" maxlength="72" required="required" id="captcha" name="captcha" placeholder="Captcha Code">
                    </td>
                    </tr>
                </table>
                <br>
            </td>
        </tr>     
    </table><br>
    <span id='info'></span>
    <br>
    <span id='info2'></span>
    <br><br>
    <input type='submit' value='Sign Up!' id='submit'>
    </form>
<?php
                            } else {
                                // They have an expired time. Remove their entry (below needs to be checked to make sure its SQL valid and works),
                                // and send them back to the first page.
                                $result = queryMySQL("DELETE FROM invite WHERE nickname='$Nickname' AND email='$Email' AND code='$Code' AND expires='$TimeFromDB';");
                                showError("You have run out of time, your link has expired. Please contact your Administrator.");
                            }
                        }
                    }
                }
            }
        }
        

    // Put out the footer
    require_once(dirname(__FILE__) . '/include/pieces/footer.php');
?>
