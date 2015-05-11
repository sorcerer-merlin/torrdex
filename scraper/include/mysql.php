<?php 
  /* Begin MySQL configuration */
  $dbhost  = 'localhost';
  $dbname  = 'torrdex';
  $dbuser  = 'torrdex';
  $dbpass  = 'fjio485K6L9e@%$';
  /* End MySQL configuration */
  
  // Connect to the MySQL database, using the config provided above, or error out
  $connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
  if ($connection->connect_error) die($connection->connect_error);
  
  function queryMysql($query)
  {
    global $connection;
    $result = $connection->query($query);
    if (!$result) showError($connection->error);
    return $result;
  }

  // Get the MySQL error
  function errorMysql()
  {
  	global $connection;
  	return($connection->error);
  }
?>
