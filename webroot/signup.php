<?php

  $pageTitle = "Sign Up";
  require_once 'header.php';
  require_once 'PasswordHash.php';
  echo "<div style='margin-left:40px;'><h3>Enter your account details below:</h3>";
  $error = $user = $fullname = $pass = $pass2 = "";

  // If sign ups are closed, redirect to main page
  if ($configOptions['allow_signup'] == "false") echo '<script type="text/javascript">window.location = "/"</script>';  

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
      request.open("POST", "checkuser.php", true)
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

  if (isset($_POST['user']))
  {
	  // The user has submitted the data, go through all the tests:
	  //	- Do we have a user by that name already?
	  //	- Check to make sure passwords match
	  //	- Start them with leecher account
	  $user = $_POST['user'];
	  $pass = $_POST['pass'];
	  $pass2 = $_POST['pass2'];
	  $fullname = $_POST['fullname'];
	  
	  // Do we already have that user name?
	  $mysqlQuery = "SELECT * FROM members WHERE user='$user';";
      $result = queryMySQL($mysqlQuery);

      if ($result->num_rows != 0) {		
	  	$error = "<span class='error'>The username you chose already exists!</span>";
	  } else {
		 // check to make sure passwords match
		if ($pass != $pass2) {
			$error = "<span class='error'>The passwords DO NOT match. Try again!</span>";
		} else {
			// make the actual account
			$PasswordHashed = PasswordHash::create_hash($pass);	
			$mysqlQuery = "INSERT INTO members VALUES ('$user', '$PasswordHashed', '0', '$fullname');";
			$result = queryMySQL($mysqlQuery);
			if (!$result) {
				$error = "<span class='error'>There was an error adding you to the database!</span>";	
			} else
				$_SESSION['user'] = $user;
				$_SESSION['pass'] = $pass;
				$_SESSION['fullname'] = $fullname;
				$_SESSION['acct_type'] = ACCT_TYPE_LEECHER;
				echo '<script type="text/javascript">window.location = "/"</script>';
		}
	  }
  }
?>
<!-- The form for the sign up is displayed here -->
    <form method='post' action='signup'><?php print $error; ?>
    <br><br>
    <table>
    	<tr>
        	<td class="rowcap">&nbsp;&nbsp;Username:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='text' maxlength='32' name='user' value='<?php print $user; ?>' autofocus='autofocus' required="required" placeholder="Username" onBlur='checkUser(this)'></td>
        </tr>
        <tr>
        	<td class="rowcap">&nbsp;&nbsp;Password:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='password' maxlength='72' id='pass' name='pass' value='<?php print $pass; ?>' required="required" placeholder="Password"></td>
        </tr>
        <tr>
        	<td class="rowcap">&nbsp;&nbsp;Re-enter Password:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='password' maxlength='72' id='pass2' name='pass2' value='<?php print $pass2; ?>' required="required" placeholder="Re-enter Password" onBlur="checkPass()"></td>
        </tr>
        <tr>
          <td class="rowcap">&nbsp;&nbsp;Display Name:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='text' maxlength='72' name='fullname' value='<?php print $fullname; ?>' autofocus='autofocus' required="required" placeholder="Display Name"></td>
        </tr>
    </table><br>
    <span id='info'></span>
    <br>
    <span id='info2'></span>
    <br><br>
    <input type='submit' value='Sign Up!' id='submit'>
    </form>
<!-- End form -->
<?php
  
  require_once('footer.php');
  
?>