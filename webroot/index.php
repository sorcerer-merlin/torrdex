<?php
	$pageTitle = "Home";
	require_once 'header.php';
    
	
	if ($loggedin) {
		$fullname = $_SESSION['fullname'];
		echo "<h3>Welcome back, $fullname!</h3><br>Thanks for coming back to see us, we appreciate your support! Check out our torrents below.<br><br><br><br>";
		
	// pull the stuff from the DB
	$result = queryMySQL("SELECT * FROM torrents ORDER BY uploaded DESC;") or die('<h3>Ooops!!!</h3><br><b><u>Error:</b></u> <i>Info-Hash not in database!<i>');
		
	// Check to make sure we have it in the database before continuing
	if ($result->num_rows == 0) {
		echo '<h3>Ooops!!!</h3><br><b><u>Error:</b></u> <i>Something\'s wrong, OR your database is empty!<i>';
	} else {
?>
		<h3>Torrents</h3>
        <table width="90%" class="sortable">
        <tr>
        	<td class="rowcap">Type:</td>
            <td class="rowcap" width="50%" style="text-align:center;">Name:</td>
            <td class="rowcap">Age:</td>
            <td class="rowcap">Size:</td>
            <td class="rowcap">Files:</td>
            <td class="rowcap">Author:</td>
        </tr>
        
<?php
	
	// Go through each one and print it out
	while($row = $result->fetch_object()) { 
		$TorrentType = $row->type;
		$TorrentName = $row->name;
		//$TorrentUploaded = $row->uploaded;
		$TorrentHash = $row->hash;
		$TorrentAuthor = getDisplayName($row->author);
		$TorrentSize = $row->size;
		$TorrentFileCount = $row->filecount;
		$TorrentAge = dateDiff(time(), intval($row->uploaded), 1) 
?>

        <tr>
        	<td class="rowdata"><?php print $TorrentType; ?></td>
            <td class="rowdata" width="300px"><a href="details?hash=<?php print $TorrentHash; ?>"><?php print $TorrentName; ?></a></td>
            <td class="rowdata" style="text-align:right;"><?php print $TorrentAge; ?></td>
            <td class="rowdata" style="text-align:center;"><?php print humanFileSize($TorrentSize); ?></td>
            <td class="rowdata" style="text-align:center;"><?php print $TorrentFileCount; ?></td>
            <td class="rowdata" style="text-align:right;" >
	            <?php
	            	if ($TorrentAuthor != "Anonymous") {
	            		print "<a href='author?name=$row->author'>$TorrentAuthor</a>";
	            	} else {
	            		print $TorrentAuthor; 
	            	}
	             ?>
	             &nbsp;<?php print isCertified($row->author); ?>
             </td>
        </tr>

<?php
	}
?>
</table>
<?php
	}
?>        
        
        

<?php
	} else {           
		//echo '<h3>Access Denied!</h3><br>You are not currently <strong>LOGGED IN</strong>. Please login above.';
		/*echo '<script type="text/javascript">window.location = "login"</script>'; */
?>

<!-- this is where we put the page that is shown when no one is logged in. News page, me thinks. -->
<!--<img src="img/JollyRoger.png" height="350" width="350" alt="TorrDex Logo" /><br />-->
<h1>TorrDex</h1>
<div align="left"><blockquote>
<h3>What is TorrDex?</h3>
In short, <strong>TorrDex</strong> is a <em>Semi-Private BitTorrent Indexing Community</em>.  It is licensed under the <a href="http://www.gnu.org/licenses/gpl-3.0-standalone.html">GNU GPLv3</a>, 
and hosted on <a href="https://github.com/sorcerer-merlin/torrdex">GitHub</a> by its creator <a href="https://github.com/sorcerer-merlin/">Sorcerer Merlin</a>. <strong>TorrDex</strong> is <u>NOT</u> a main-stream entry-level
web application developed by a team of dedicated programmers.  It is a hobby-project developed by <strong>ONE</strong> intermediate-level+&#8482; hobbyist programmer. It is therefore subject to bugs and other issues, 
which should be reported at the repository <a href="https://github.com/sorcerer-merlin/torrdex/issues">Issues</a> page. Any feature requests and the like can also be submitted there as well.
<h3>Technical Specs</h3>
<strong>TorrDex</strong> is built using <a href="http://en.wikipedia.org/wiki/HTML5">HTML5</a>, <a href="http://php.net/">PHP5</a>, <a href="http://www.mysql.com/">MySQL</a>, <a href="http://en.wikipedia.org/wiki/JavaScript">JavaScript</a>
, and <a href="http://en.wikipedia.org/wiki/Ajax_%28programming%29">AJAX</a>. Account passwords are encrypted, using the <a href="https://github.com/defuse/password-hashing">PasswordHash</a> class for PHP 
developed by <a href="https://github.com/defuse">Taylor Hornby</a>. BitTorrent processing and support is provided by the <a href="https://github.com/christeredvartsen/php-bittorrent">PHP_BitTorrent</a> library 
(in PHAR format) developed by <a href="https://github.com/christeredvartsen">Christer Edvartsen</a>. The entire color scheme and theme for <strong>TorrDex</strong> is completely dynamic and achieved using <a href="http://en.wikipedia.org/wiki/Cascading_Style_Sheets">CSS</a>
 and <a href="http://www.cssfontstack.com/Web-Fonts">Web Fonts</a> (which <strong>MAY</strong> allow for additional theming support in the future!).
 <h3>Feature List</h3>
 Below is a list of completely finished features incorporated into <strong>TorrDex</strong>.  For incomplete or planned features, look at the next section.
 <ul>
 	<li>3 types of User Accounts with encrypted Passwords and Display Names</li>
 	<li>Profile picture (avatar) support</li>
 	<li>New User sign up's available only when enabled in the Administration panel</li>
 	<li>Session-based login system, with modifiable User profiles</li>
 	<li>Searchable database of Torrents with sortable Table columns</li>
 	<li>Ability to Upload Torrents to database</li>
 	<li>Administration panel with the ability to make changes and remove users, as well as enable/disable global options that change functionality of the site</li>
 	<li>Customizable Torrent Description templates for new uploads to ease in the writing of Torrent Descriptions</li>
 </ul>
 <h3>To-Do List</h3>
 This list of features and ideas is not yet implemented in <strong>TorrDex</strong>. They may have partially working code, or not even be coded at all. Look for them in future releases of the site.
 <ul>
 	<li>Theme support</li>
 	<li>Torrent comment/rating system</li>
 	<li>Certified Uploader (aka the Green Skull) system</li>
 	<li>Email invite system using encrypted GUIDs</li>
 </ul>
</blockquote></div><br /><br />
<!-- end news page or whatever -->

<?php
	}
?>
	</td>
  </tr>
<?php  
    require_once 'footer.php';
?>