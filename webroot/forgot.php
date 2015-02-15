<?php
    // Put out the header
    $pageTitle = "Forgot Username/Password";
    require_once(dirname(__FILE__) . '/include/pieces/header.php');

// Add our script
 echo <<<_END
  <script type="text/javascript">

    function checkNewPass()
    {
      pass1 = O('newpass-hidden').value
      pass2 = O('newpass2-hidden').value
      if (pass1 == pass2) {
        O('info_newpass').innerHTML = "<span class='available'>&nbsp;&#x2714; The passwords match.</span>"
      } else {
        O('info_newpass').innerHTML = "<span class='taken'>&nbsp;&#x2718; The passwords don't match!</span>"
      }
      
    }
    function changePass()
    {
        user = O('user').innerHTML
        pass1 = O('newpass-hidden').value
        pass2 = O('newpass2-hidden').value
        if (pass1 == pass2) {
          // reset the info to nothing on oldpass 
          O('info_oldpass').innerHTML = ''

          // get the pass and user here and pass it off 
          params  = "user=" + user + "&pass=" + pass1
          request = new ajaxRequest()
          request.open("POST", "post/changepass.php", true)
          request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
          //request.setRequestHeader("Content-length", params.length)
          //request.setRequestHeader("Connection", "close")

          request.onreadystatechange = function()
          {
            if (this.readyState == 4)
              if (this.status == 200)
                if (this.responseText != null)
                  window.location = "/login" // O('info_newpass').innerHTML = this.responseText
          }
          request.send(params) 
        }
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

    // If we didn't specify a mode, redirect to the login page
    if (!isset($_GET['mode'])) echo '<script type="text/javascript">window.location = "/login"</script>';

    // Find out which mode we are in for the multi-function script
    $Mode = $_GET['mode'];
    if ($Mode == "form") {
?>
<h3>Enter your email address:</h3>
<form action="forgot" method="GET">
<input type="hidden" name="mode" value="form_submitted">
<table>      
    <tr>
      <td class="rowcap">&nbsp;&nbsp;Email:&nbsp;&nbsp;</td>
      <td class="rowdata">
        <input type="email" maxlength="255" id="email" name="email" value="" required="required" placeholder="Email Address">
      </td>
    </tr>
</table>
<br><br>
<input type='submit' value='Reset Password' id='submit'>
</form>
<?php
    }

    if ($Mode == "form_submitted") {
        // Check for errors, do we have a non-set email field, or blank email, or email that isn't in our database
        if (!isset($_GET['email'])) echo '<script type="text/javascript">window.location = "/forgot?mode=form"</script>';
        if ($_GET['email'] == "") echo '<script type="text/javascript">window.location = "/forgot?mode=form"</script>';

        // Check the DB
        $Email = $_GET['email'];
        $result = queryMySQL("SELECT user FROM members WHERE email='$Email';");
        if ($result->num_rows == 0) echo '<script type="text/javascript">window.location = "/forgot?mode=form"</script>';

        // Get the other info we need
        $row = $result->fetch_object();
        $User = $row->user;

        // Also need to check the FORGOT table and find out if they already have a request in, which case they need
        // to wait or we could do something other kind of security protection.
        $result = queryMySQL("SELECT user,email FROM forgot WHERE user='$User' AND email='$Email';");
        if ($result->num_rows >= 1)
            showError("You have already attempted this procedure. Please contact your Administrator.");
        else {
            // Now build a random code
            $Code = substr(hash('sha256', mt_rand()), 0, 50);

            // Get current time + 1 hour
            $Expires = strtotime('+1 hour');
            
            /* DEBUG
            echo $User . "<br>";
            echo $Email . "<br>";
            echo $Code . "<br>";
            echo date("F j, Y, H:i", $Expires) . "<br>";*/

            // Insert into DB
            $result = queryMySQL("INSERT INTO forgot VALUES ('$User', '$Email', '$Code', '$Expires');");
            //if (!$result || $result->num_rows == 0) showError('There was an error processing that action. Please contact your Administrator!');

            // Finally we create the link
            $Link = $configOptions_Strings['site_root'] . "forgot?mode=do_reset&user=" . $User . "&email=" . $Email . "&code=" . $Code;
            //echo "<a href='" . $Link . "'>Reset Password</a>";
            echo "<h3>Verification</h3><br>An email has been dispatched to your email address with a verification link needed to complete this process. <br><br>Please check your email. If you do not receive an email, please contact your Administrator!";

            // Email the reset link and instructions to the user, and then output some kind of page saying it has been done and they have an
            // hour before it expires.
            sendEmail($User, $Email, $Link);
        }
    }

    if ($Mode == "do_reset") {
        // Check the variables to make sure they exist, and aren't blank. Then check the code for expiration and validity in DB.
        // try to think of any other reasons we might have issues.

        // Check for no exists variables
        if (!isset($_GET['user']) && !isset($_GET['email']) && !isset($_GET['code'])) {
            showError("Improper parameters were passed! Please contact your Administrator.");
        } else {
            // Variables exist, grab the variables
            $User = $_GET['user'];
            $Email = $_GET['email'];
            $Code = $_GET['code'];

            // Check for blank variables
            if ($User == "" || $Email == "" || $Code == "") {
                showError("Improper parameters were passed! Please contact your Administrator.");
            } else {
                /* DEBUG 
                echo $User . "<br>";
                echo $Email . "<br>";
                echo $Code . "<br>"; */

                // Check to see if the user/email pair matches.
                $result = queryMySQL("SELECT user,email FROM members WHERE user='$User';");

                // They email and user do not match, don't let them know which for security purposes
                if ($result->num_rows == 0)
                    showError("Improper parameters were passed! Please contact your Administrator.");
                else {
                    // Now we need to check the DB for a code and time request that matches our user/email
                    $result = queryMySQL("SELECT * FROM forgot WHERE user='$User' AND email='$Email';");

                    // There is no entry for the user/email combo, i.e. no good
                    if ($result->num_rows == 0)
                        showError("Improper parameters were passed! Please contact your Administrator.");
                    else {
                        // Now we have a valid combo of user/email that's in the DB with a code. Time to check the code
                        // and then check the expiration time.
                        $row = $result->fetch_object();
                        $CodeFromDB = $row->code;
                        $TimeFromDB = $row->expires;

                        // Check Code first
                        if ($Code != $CodeFromDB)
                            showError("Improper parameters were passed! Please contact your Administrator.");
                        else {
                            // Now check the time for expiration
                            $Now = time();
                            if ($Now <= $TimeFromDB) {
                                // Do the password reset form, and make use of CAPTCHA or something. Fun stuff.

                                // Do the CAPTCHA stuff.
                                //session_start(); <---- should have already happened by this point
                                $_SESSION = array();
                                require_once(dirname(__FILE__) . '/include/libs/captcha/simple-php-captcha.php');
                                $_SESSION['captcha'] = simple_php_captcha();
?>
                                <h3>Reset your password:</h3>
                                <form action="verify" method="POST">
                                <input type="hidden" name="mode" value="pass_reset">
                                <input type="hidden" name="user" value="<?php print $User; ?>">
                                <input type="hidden" name="email" value="<?php print $Email; ?>">
                                <input type="hidden" name="code" value="<?php print $Code; ?>">
                                <input type="hidden" name="time" value="<?php print $TimeFromDB; ?>">
                                <table> 
                                    <tr>
                                        <td class="rowcap">Security:</td>
                                        <td class="rowdata">
                                            <br>
                                            <table>
                                                <tr>
                                                <td align="right" colspan="2"><img src="<?php print $_SESSION['captcha']['image_src']; ?>" alt="CAPTCHA"></td>
                                                </tr>
                                                <tr>
                                                <td align="right">Enter code:</td>
                                                <td>
                                                    <input type="text" maxlength="72" required="required" id="captcha" name="captcha" placeholder="Captcha Code">
                                                </td>
                                                </tr>
                                            </table>
                                            <br>
                                        </td>
                                    </tr>     
                                    <tr>
                                        <td class="rowcap">Password:</td>
                                        <td class="rowdata">
                                        <br>
                                        <table>
                                        <tr>
                                            <td align="right">New Password:</td>
                                            <td><input type="password" maxlength="72" name="newpass" id="newpass-field" required="required" placeholder="New Password"></td>
                                        </tr>
                                        <tr>
                                            <td align="right">Re-type:</td>
                                            <td><input type="password" maxlength="72" name="newpass2" id="newpass2-field" required="required" placeholder="Confirm New Password" onblur="checkNewPass()"></td>
                                        </tr>
                                        </table>
                                        <br>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                <span id='info_newpass'></span><br><br>
                                <input type='submit' value='Reset Password' id='submit'>
                                </form>
                                <script type="text/javascript">
                                  new MaskedPassword(document.getElementById("newpass-field"), '\u25C6'); //'\u25CF');
                                </script>
                                <script type="text/javascript">
                                  new MaskedPassword(document.getElementById("newpass2-field"), '\u25C6'); //'\u25CF');
                                </script>
<?php


                                // After they do the whole reset, make them login again.
                            } else {
                                // They have an expired time. Remove their entry (below needs to be checked to make sure its SQL valid and works),
                                // and send them back to the first page.
                                $result = queryMySQL("DELETE FROM forgot WHERE user='$User' AND email='$Email' AND code='$CodeFromDB' AND expires='$TimeFromDB';");
                                showError("You have run out of time, your link has expired. Please <a href='forgot?mode=form'>try again</a>.");
                            }
                        }
                    }
                }
            }
        }
    }

    // Put out the footer
    require_once(dirname(__FILE__) . '/include/pieces/footer.php');
?>
