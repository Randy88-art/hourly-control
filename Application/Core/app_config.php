<?php	
	session_start();
	session_regenerate_id();

	/** Define root folder */
	define("SITE_ROOT", $_SERVER['DOCUMENT_ROOT']);	

	/** Define database configuration file */
	define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db.config.php');				
	
	/** Define connection */
	require_once(SITE_ROOT . "/../Application/Core/connect.php");
	define('DB_CON', $dbcon);
	
	/** Define max number of pages in pagination */
	define("MAX_ROWS_PER_PAGES", 8);
	define("MAX_ITEMS_TO_SHOW", 5); // Show 5 items per page in pagination buttons
?>
