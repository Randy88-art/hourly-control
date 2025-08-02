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
	define("MAX_PAGES", 10);
?>
