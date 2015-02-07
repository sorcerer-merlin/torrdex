<?php 
  // Start the cookie session for the login variables
  session_start();
  require_once 'functions.php';
?>   
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php print $configOptions_Strings['site_title'] . " - ". $pageTitle; ?></title>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Aclonica" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Unlock" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Snippet" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Merriweather" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Sigmar+One" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Inconsolata" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Atomic+Age" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Spirax" />
<link href="/style.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="icon" type="image/x-icon" />
<script type="text/javascript" src="ibox/ibox.js"></script>
<link rel="stylesheet" href="ibox/skins/darkbox/darkbox.css" type="text/css" media="screen"/>
<script src='javascript.js'></script>
<script src="sorttable.js"></script> 
<script type="text/javascript" src="MaskedPassword.js"></script>
</head>
<?php
  


  if (isset($_SESSION['user']))
  {
    $user     = $_SESSION['user'];
    $loggedin = TRUE;
  }
  else $loggedin = FALSE;

  // NAME & EMAIL UPDATE HACK
  if (isset($_SESSION['fullname'])) {
    $result = queryMySQL("SELECT user,fullname,email FROM members WHERE user='$user'");
    $row = $result->fetch_object();
    $DBfullname = $row->fullname;
    $DBemail = $row->email;
    if ($_SESSION['fullname'] != $DBfullname) $_SESSION['fullname'] = $DBfullname;
    if ($_SESSION['email'] != $DBemail) $_SESSION['email'] = $DBemail;
  }

?>

<body color="#00aeff">
<table align="center" class="mytable"  width="1240px" border="1">
  <caption >
    <?php print $configOptions_Strings['table_caption']; ?>
  </caption>
  <thead><tr class="mytable">
    <th scope="col">Navigation</th>
  </tr></thead>
  <tr class="mytable">
  <td class="mytable" align="center">
  <div class="table">
  <div class="navbar"><ul>
<?php
  // Special links to pages with options showing/hiding them are listed below, followed
  // by their logic statements to enable/disable based on mySQL configuration options
  // table.
  $signup_link = "<li><a href='signup'>Sign Up</a></li>";
  $upload_link = "<li><a href='upload'>Upload</a></li>";
  $admin_link = "<li><a href='admin'>Admin</a></li>";
  $invite_link = "<li><a href='invite?mode=do_form'>Invite</a></li>";
  
  // Output the menu
  if ($loggedin)
  {
	  	// Check to see if we are only allowing premium (and admin) accounts to upload and then
		  // enable/disable link appropriately
	  	if ($configOptions_Booleans['only_seeder_uploads'] == "true") {
	  		if ($_SESSION['acct_type'] == ACCT_TYPE_LEECHER) $upload_link = "";
  		}

      // Check to see if we are an Administrator, if so we can leave that link in there, if not
      // make it disappear
      if ($_SESSION['acct_type'] != ACCT_TYPE_ADMIN) $admin_link = "";

      // Check to see if we are an Administrator and Only admins can send out sign up emails,
      // or we are atleast a Seeder and then leave the invite link alone, otherwise blank it.
      if ($configOptions_Booleans['only_admin_invites'] == "true")
        if ($_SESSION['acct_type'] != ACCT_TYPE_ADMIN) 
          $invite_link = "";
      else
        if ($_SESSION['acct_type'] == ACCT_TYPE_LEECHER)
          $invite_link = "";

		
		// Finally output the constructed member menu

		echo "<li><a href='logout'>Logout</a></li>";
    echo "<li><a href='profile'>Profile</a></li>";
    echo $invite_link;
    echo $admin_link;
    echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>";
    echo "<li><a href='/'>Home</a></li>";
    echo "<li><a href='listby?mode=browse&param=none'>Browse</a></li>";
    echo "<li><a href='search'>Search</a></li>";
    echo $upload_link;
  }
  else
  {
		// Check to see if we are allowing sign ups at this time, enable/disable menu item as
		// appropriate.	  
	  	if ($configOptions_Booleans['allow_signup'] == "false") $signup_link = "";
	  
		// Finally output the constructed public menu
    echo "<li><a href='login'>Login</a></li>";
    echo $signup_link;
    echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>";
    echo "<li><a href='/'>Home</a></li>";
  }
?>
</ul></div></div>
</td>
</tr>
  <thead><tr class="mytable">
    <th scope="col"><?php print $pageTitle; ?></th>
  </tr></thead>
  <tr class="mytable" height="500px">
  	<td class="mytable" align="center">