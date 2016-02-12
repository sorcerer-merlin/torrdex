<?php
// Get the MySQL configuration
require_once (dirname(__FILE__) . '/config/mysql.php');

// Set the time zone
date_default_timezone_set("UTC");

// Connect to the MySQL database, using the config provided above, or error out
$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($connection->connect_error) die($connection->connect_error);

// Initialize the variables we use to hold the options stored in the MySQL DB
$configOptions_Booleans = [];
$configOptions_Integers = [];
$configOptions_Strings = [];

// Load the actual options
loadOptionsfromMySQL();

/* Define Account Type Constants */
define("ACCT_TYPE_LEECHER", 0);
define("ACCT_TYPE_SEEDER", 1);
define("ACCT_TYPE_ADMIN", 2);
$AccountTypes[ACCT_TYPE_LEECHER] = "Leecher";
$AccountTypes[ACCT_TYPE_SEEDER] = "Seeder";
$AccountTypes[ACCT_TYPE_ADMIN] = "Admin";

/* Define Torrent Type Constants */
define("TORR_TYPE_ALL", -1);
define("TORR_TYPE_APP", 0);
define("TORR_TYPE_GAME", 1);
define("TORR_TYPE_MOVIE", 2);
define("TORR_TYPE_TV", 3);
define("TORR_TYPE_MUSIC", 4);
define("TORR_TYPE_OTHER", 5);
$TorrentTypes[TORR_TYPE_APP] = "App";
$TorrentTypes[TORR_TYPE_GAME] = "Game";
$TorrentTypes[TORR_TYPE_MOVIE] = "Movie";
$TorrentTypes[TORR_TYPE_TV] = "TV";
$TorrentTypes[TORR_TYPE_MUSIC] = "Music";
$TorrentTypes[TORR_TYPE_OTHER] = "Other";
define("DEFAULT_TORR_TYPE", TORR_TYPE_OTHER);

/* Define Constants for Sorting w/ Pagination */
define("SORT_ARROW_DSC", "&#x25BE;"); // ARROW DN   --- descending = largest to smallest 55, 14, 8, 3
define("SORT_ARROW_ASC", "&#x25B4;"); // ARROW UP   --- ascending = smallest to largest   3, 8, 14, 55

// Run MySQL query and return the result object
function queryMysql($query) {

    global $connection;
    $result = $connection->query($query);
    
    if (!$result) showError($connection->error);
    return $result;
}

// Get the MySQL error
function errorMysql() {

    global $connection;
    return ($connection->error);
}

// Get the sort order icon
function getSortIcon($order) {
    
    if ($order == "desc") return (SORT_ARROW_DSC);
    elseif ($order == "asc") return (SORT_ARROW_ASC);
}

// Get a valid column to sort by
function isValidSortColumn($column) {

    $valid_columns = array("type", "name", "uploaded", "seeders", "leechers", "size", "filecount", "author");
    return (in_array($column, $valid_columns));
}

// Get a valid sort order
function isValidSortOrder($order) {

    $valid_orders = array("asc", "desc");
    return (in_array($order, $valid_orders));
}

// Get votes for torrent hash
function getVotes($hash, $type) {

    $result = queryMySQL("SELECT * FROM ratings WHERE vote='$type' AND hash='$hash';");
    return ($result->num_rows);
}

// Have they already voted for the specified torrent?
function hasVoted($User, $Hash) {

    $result = queryMySQL("SELECT hash,user,vote FROM ratings WHERE user='$User' AND hash='$Hash';");
    
    if ($result->num_rows == 0) return false;
    else return true;
}

// Grab their actual vote for this torrent
function getVote($User, $Hash) {

    $result = queryMySQL("SELECT hash,user,vote FROM ratings WHERE user='$User' AND hash='$Hash';");
    
    if ($result->num_rows == 0) return NULL;
    else {
        $row = $result->fetch_object();
        return ($row->vote);
    }
}

// Torrent in Database BOOL by
function isTorrentinDatabase($hash) {

    $TorrentExists = FALSE;
    $result = queryMySQL("SELECT * FROM torrents WHERE hash='$hash';");
    
    if ($result->num_rows != 0) $TorrentExists = TRUE;
    return ($TorrentExists);
}

// Get the name of the Torrent Author
function getTorrentAuthor($hash) {

    $TorrentExists = FALSE;
    $result = queryMySQL("SELECT * FROM torrents WHERE hash='$hash';");
    
    if ($result->num_rows == 0) return (NULL);
    else $row = $result->fetch_object();
    return ($row->author);
}
function countTorrentsSearch($type, $keywords) {
    
    if ($type == - 1) $result = queryMySQL("SELECT * FROM torrents WHERE  name LIKE '%" . $keywords . "%' ORDER BY uploaded DESC;");
    else $result = queryMySQL("SELECT * FROM torrents WHERE  name LIKE '%" . $keywords . "%' AND type='" . $type . "' ORDER BY uploaded DESC;");
    return ($result->num_rows);
}
function countTorrents($mode, $param) {
    
    if ($mode == "browse") $result = queryMySQL("SELECT * FROM torrents;");
    elseif ($mode == "author") $result = queryMySQL("SELECT * FROM torrents WHERE author='" . $param . "';");
    elseif ($mode == "type") $result = queryMySQL("SELECT * FROM torrents WHERE type='" . $param . "';");
    return ($result->num_rows);
}

// Destroy cookie session
function destroySession() {

    $_SESSION = array();
    
    if (session_id() != "" || isset($_COOKIE[session_name() ])) setcookie(session_name(), '', time() - 2592000, '/');
    session_destroy();
}
function showError($var) {

    echo "<h3>Ooops!!!</h3>";
    echo "<span class='error'>$var</span>";
}

// Get the status of a certified member by name
function isCertified($user, $return_type = "image") {

    // Grab the options, and whether or not we are showing authors globally
    global $configOptions_Booleans;
    global $configOptions_Strings;
    $ShowAuthors = $configOptions_Booleans['show_authors'];

    // Attempt to find the row in the certified table
    $queryString = "SELECT user FROM certified WHERE user='$user';";
    $result = queryMysql($queryString);

    // Decide what to do if we don't have a row returned based on return type
    if ($result->num_rows == 0) {

        // Switch based on the return type we are requesting (NOT CERTIFIED)
        switch ($return_type) {
            case "bool":
                return FALSE;
                break;
            case "image":
                return "";
                break;
            case "radio":
                return "<option value='true'>Yes</option><option value='false' selected='selected'>No</option>";
                break;
            default:
                // invalid option type
                break;
        }
    } else {

        // Switch based on the return type we are requesting (CERTIFIED)
        switch ($return_type) {
            case "bool":
                return TRUE;
                break;
            case "image":
                return "<img src='/style/" . $configOptions_Strings['theme_name'] . "/img/skull-icon.png' height='16' width='16' alt='Certified Uploader' />";
                break;
            case "radio":
                return "<option value='true' selected='selected'>Yes</option><option value='false'>No</option>";
                break;
            default:
                // invalid option type                
                break;
        }
    }
}

// Send an email with a verification link for a password reset
function sendEmailPassReset($user, $to, $link) {

    // Grab their Display Name
    $result = queryMysql("SELECT fullname FROM members WHERE user='$user';");
    $row = $result->fetch_object();
    $DisplayName = $row->fullname;

    // Set the variables
    $Head = "Reset Password Verification";
    $Subhead = "Dear " . $DisplayName . ",";
    $LinkName = "Complete Password Reset";

    // Set the paragraph text for the email
    $Para = "
        You have requested to reset the password on your account.&nbsp; If you did not make this request, please contact your
        Administrator. &nbsp;Please use the link below to complete the verification process and reset your password.<br><br>
        If you have forgotten your Username, it is <strong>$user</strong>.<br><br>
    ";

    // Do the actual emailing w/ the template
    sendEmailfromTemplate($to, $Head, $Subhead, $Para, $link, $LinkName);
}

// Send an email with a verification link for a new member invite
function sendEmailInvite($nickname, $to, $link) {

    // Set the variables
    $Head = "Member Invite";
    $Subhead = "Dear " . $nickname . ",";
    $LinkName = "Complete Member Sign Up";

    // Set the paragraph text for the email
    $Para = "
      You have been invited to join the Semi-Private BitTorrent Community, TorrDex. &nbsp;A current member has selected you
      personally for an invite. &nbsp;If you are not interested, please discard this email. &nbsp;Please use the link below to complete
      the verification process and create your new account.<br><br>
    ";

    // Do the actual emailing w/ the template
    sendEmailfromTemplate($to, $Head, $Subhead, $Para, $link, $LinkName);
}

// Send an email using the template we created
function sendEmailfromTemplate($To, $Heading, $Subheading, $Paragraph, $LinkAddress, $LinkText) {

    // First we are going to grab the contents of the email template, via the relative
    // path based on the current script
    $TPL = file_get_contents(dirname(__FILE__) . '/email/template.dat');

    // Pull the global variables from outside the function
    global $configOptions_Strings;
    $SiteTitle = $configOptions_Strings['site_title'];
    $SiteRoot = $configOptions_Strings['site_root'];
    $SiteAdmin = $configOptions_Strings['site_email'];

    // Replace all of the placeholders with their actual variables
    $Heading = $SiteTitle . " " . $Heading;
    $LogoPath = $SiteRoot . "/img/torrdex_logo.png";
    $TPL = str_replace('{{heading_text}}', $Heading, $TPL);
    $TPL = str_replace('{{subheading_text}}', $Subheading, $TPL);
    $TPL = str_replace('{{paragraph_placeholder}}', $Paragraph, $TPL);
    $TPL = str_replace('{{link_address}}', $LinkAddress, $TPL);
    $TPL = str_replace('{{link_text}}', $LinkText, $TPL);
    $TPL = str_replace('{{site_root}}', $SiteRoot, $TPL);
    $TPL = str_replace('{{logo_path}}', $LogoPath, $TPL);

    // TODO: Until we can find another solution, the only way I know of to offer a
    // view email in web browser link is to add the body of the message to some
    // sort of MySQL table and then delete it later or something -- IDK.

    // Build the email variables, and then send the email
    $Subject = $Heading;
    $Headers = 'MIME-Version: 1.0' . "\r\n";
    $Headers.= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $Headers.= 'From: Auto Administrator <' . $SiteAdmin . '>' . "\r\n";
    $Message = $TPL;
    mail($To, $Subject, $Message, $Headers);
}

// Get the display name from the user
function getDisplayName($var, $anonOK = true) {

    global $configOptions_Booleans;
    
    if ($configOptions_Booleans['show_authors'] == "false" && $anonOK) return "Anonymous";
    $queryString = "SELECT fullname FROM members WHERE user='$var';";
    $result = queryMysql($queryString);
    
    if ($result->num_rows == 0) {
        return "Anonymous";
    } else {
        $row = $result->fetch_object();
        return $row->fullname;
    }
}

// Get the account type of a given user
function getAccountType($user, $returnString = true) {

    global $AccountTypes;
    $result = queryMySQL("SELECT user,acct_type FROM members WHERE user='$user';");
    
    if ($result->num_rows == 0) {
        
        if ($returnString) return $AccountTypes[ACCT_TYPE_LEECHER];
        else return ACCT_TYPE_LEECHER;
    } else {
        $row = $result->fetch_object();
        
        if ($returnString) return $AccountTypes[$row->acct_type];
        else return $row->acct_type;
    }
}

// Escape quotes and single quotes
function EscapeQuotes($var) {

    global $connection;
    return $connection->real_escape_string($var);
}

// Completely sanitize the string provided
function sanitizeString($var) {

    global $connection;
    $var = strip_tags($var);
    $var = htmlentities($var);
    $var = stripslashes($var);
    return $connection->real_escape_string($var);
}

// returns a human readable file size based on bytes
function humanFileSize($size) {

    if ($size >= 1073741824) {
        $fileSize = round($size / 1024 / 1024 / 1024, 1) . ' GB';
    } elseif ($size >= 1048576) {
        $fileSize = round($size / 1024 / 1024, 1) . ' MB';
    } elseif ($size >= 1024) {
        $fileSize = round($size / 1024, 1) . ' KB';
    } else {
        $fileSize = $size . ' b';
    }
    return $fileSize;
}

// Does exactly what it says it does -- loads the options from our MySQL DB
function loadOptionsfromMySQL() {

    // Pull the arrays for the options so we can write to them and not variables inside this function... duh!
    global $configOptions_Booleans;
    global $configOptions_Integers;
    global $configOptions_Strings;

    // Grab all of the available options from the DB and drop them into the proper arrays based on type
    $result = queryMySQL("select * from options");
    
    if ($result->num_rows != 0) {
        while ($row = $result->fetch_object()) {

            // Get the option's info
            $opn_name = $row->name;
            $opn_value = $row->value;
            $opn_type = $row->type;

            // Add it to the appropriate array based on type of variable
            
            switch ($opn_type) {
                case "bool":
                    $configOptions_Booleans["$opn_name"] = $opn_value;
                    break;
                case "int":
                    $configOptions_Integers["$opn_name"] = $opn_value;
                    break;
                case "string":
                    $configOptions_Strings["$opn_name"] = $opn_value;
                    break;
                default:
                    // invalid option type
                    break;
            }
        }
    }
}

// Time format is UNIX timestamp or
// PHP strtotime compatible strings
function dateDiff($time1, $time2, $precision = 6) {

    // If not numeric then convert texts to unix timestamps
    if (!is_int($time1)) {
        $time1 = strtotime($time1);
    }
    
    if (!is_int($time2)) {
        $time2 = strtotime($time2);
    }

    // If time1 is bigger than time2
    // Then swap time1 and time2
    if ($time1 > $time2) {
        $ttime = $time1;
        $time1 = $time2;
        $time2 = $ttime;
    }

    // Set up intervals and diffs arrays
    $intervals = array('year', 'month', 'day', 'hour', 'minute', 'second');
    $diffs = array();

    // Loop thru all intervals
    foreach ($intervals as $interval) {

        // Create temp time from time1 and interval
        $ttime = strtotime('+1 ' . $interval, $time1);

        // Set initial values
        $add = 1;
        $looped = 0;

        // Loop until temp time is smaller than time2
        while ($time2 >= $ttime) {
            // Create new temp time from time1 and interval
            $add++;
            $ttime = strtotime("+" . $add . " " . $interval, $time1);
            $looped++;
        }
        $time1 = strtotime("+" . $looped . " " . $interval, $time1);
        $diffs[$interval] = $looped;
    }
    $count = 0;
    $times = array();

    // Loop thru all diffs
    foreach ($diffs as $interval => $value) {
        // Break if we have needed precission
        if ($count >= $precision) {
            break;
        }

        // Add value and interval
        // if value is bigger than 0
        if ($value > 0) {

            // Add s if value is not 1
            if ($value != 1) {
                $interval.= "s";
            }

            // Add value and interval to times array
            $times[] = $value . " " . $interval;
            $count++;
        }
    }

    // Return string with times
    return implode(", ", $times);
}
?>