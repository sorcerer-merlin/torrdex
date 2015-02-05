<?php 
  $pageTitle = "Login";
  require_once 'header.php';
  require_once 'PasswordHash.php';
  echo "<div style='margin-left:40px;'><h3>Enter your login details below:</h3>";
  $error = $user = $pass = "";

  if (isset($_POST['user']))
  {
    $user = sanitizeString($_POST['user']);
    $pass = $_POST['pass'];//sanitizeString($_POST['pass']);
    
    if ($user == "" || $pass == "")
        $error = "Not all fields were entered<br>";
    else
    {
	  // Grab the username from the DB based on what was posted	
      $result = queryMySQL("SELECT * FROM members WHERE user='$user'");

	  // Here the user name doesn't exist
      if ($result->num_rows == 0)
      {
        $error = "<span class='error'>Username/Password is invalid!</span><br><br>";
      }
      else
      {
		// Grab the password hash
		$row = $result->fetch_object();
		$StoredHash = $row->pass;
		$fullname = $row->fullname;
		$acct_type = $row->acct_type;
    $email = $row->email;
		
		// Now we need to check the password entered against the hash
		if (PasswordHash::validate_password($pass, $StoredHash)) {
			// save all of the important account info to our cookie session in case we need it readily available
			$_SESSION['user'] = $user;
      $_SESSION['email'] = $email;
			$_SESSION['pass'] = $pass;
			$_SESSION['fullname'] = $fullname;
			$_SESSION['acct_type'] = $acct_type;
				
			// Use JS to re-direct to the main page
			if (isset($_POST['page'])) {
				$page = $_POST['page'];
				echo '<script type="text/javascript">window.location = "' . $page . '"</script>';
			} else
				echo '<script type="text/javascript">window.location = "/"</script>';
		} else {
			$error = "<span class='error'>Username/Password is invalid!</span><br><br>";
		}
      }
    }
  }

?>  
    <form method='post' action='login'><?php print $error; ?>
    <br>
    <table>
    	<tr>
        	<td class="rowcap">&nbsp;&nbsp;Username:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='text' maxlength='32' name='user' id='user' value='<?php print $user; ?>' autofocus='autofocus' required="required" placeholder="Username"></td>
        </tr>
        <tr>
        	<td class="rowcap">&nbsp;&nbsp;Password:&nbsp;&nbsp;</td>
            <td class="rowdata"><input type='password' maxlength='72' name='pass' id='pass-field' value='<?php print $pass; ?>' required="required" placeholder="Password"></td>
        </tr>
    </table>
<?php
	if (isset($_GET['page'])) {
		// TODO: add a hidden variable with the page we want to view after this in it on the post form
			echo '<input type="hidden" name="page" value="' . $_GET['page'] .'">';
	}

?>
    <br><br>
    <span class='fieldname'>&nbsp;</span>
    <input type='submit' value='Login' id='submit'>
    </form><br></div>
  <script type="text/javascript">
  
    //apply masking to the password field
    //pass the field reference, masking symbol, and character limit
    new MaskedPassword(document.getElementById("pass-field"), '\u25C6'); //'\u25CF');
  </script>
<?php
  require_once 'footer.php';
?>