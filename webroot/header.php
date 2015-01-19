<?php 
  // Start the cookie session for the login variables
  session_start();
?>  

<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TorrDex - <?php print $pageTitle; ?></title>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Aclonica" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Unlock" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Snippet" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Merriweather" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Sigmar+One" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Inconsolata" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Atomic+Age" />
<link href="/style.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="icon" type="image/x-icon" />
<script type="text/javascript" src="ibox/ibox.js"></script>
<link rel="stylesheet" href="ibox/skins/darkbox/darkbox.css" type="text/css" media="screen"/>
<script src='javascript.js'></script>
<script src="sorttable.js"></script> 
</head>
  
<?php
  require_once 'functions.php';


  if (isset($_SESSION['user']))
  {
    $user     = $_SESSION['user'];
    $loggedin = TRUE;
  }
  else $loggedin = FALSE;

  // NAME HACK
  if (isset($_SESSION['fullname'])) {
    $result = queryMySQL("SELECT user,fullname FROM members WHERE user='$user'");
    $row = $result->fetch_object();
    $DBfullname = $row->fullname;
    if ($_SESSION['fullname'] != $DBfullname) $_SESSION['fullname'] = $DBfullname;
  }

?>

<body color="#00aeff">
<table align="center" class="mytable"  width="1240px" border="1">
  <caption >
    . : = | Sorcerer Merlin's TORRent inDEXer | = : .
  </caption>
  <thead><tr class="mytable">
    <th scope="col">Navigation</th>
  </tr></thead>
  <tr class="mytable">
  <td class="mytable" align="center">
<?php
  // Special links to pages with options showing/hiding them are listed below, followed
  // by their logic statements to enable/disable based on mySQL configuration options
  // table.
  $signup_link = " <a href='signup'>Sign Up</a> |";
  $upload_link = " <a href='upload'>Upload</a> |";
  $admin_link = " <a href='admin'>Admin</a> |";
  
  // Output the menu
  if ($loggedin)
  {
	  	// Check to see if we are only allowing premium (and admin) accounts to upload and then
		  // enable/disable link appropriately
	  	if ($configOptions['only_seeder_uploads'] == "true") {
	  		if ($_SESSION['acct_type'] == ACCT_TYPE_LEECHER) $upload_link = "";
  		}

      // Check to see if we are an Administrator, if so we can leave that link in there, if not
      // make it disappear
      if ($_SESSION['acct_type'] != ACCT_TYPE_ADMIN) $admin_link = "";
		
		// Finally output the constructed member menu
		echo "| <a href='logout'>Logout</a> | <a href='profile'>Profile</a> |" . $admin_link . " . . . . . | <a href='/'>Home</a> | <a href='search'>Search</a> |" . $upload_link;
  }
  else
  {
		// Check to see if we are allowing sign ups at this time, enable/disable menu item as
		// appropriate.	  
	  	if ($configOptions['allow_signup'] == "false") $signup_link = "";
	  
		// Finally output the constructed public menu	  
		echo "| <a href='login'>Login</a> |" . $signup_link . " . . . . . | <a href='/'>Home</a> |";
  }
?>
</td>
</tr>
  <thead><tr class="mytable">
    <th scope="col"><?php print $pageTitle; ?></th>
  </tr></thead>
  <tr class="mytable" height="500px">
  	<td class="mytable" align="center">