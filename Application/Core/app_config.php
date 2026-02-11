<?php	
	session_start();	

	/** Define root folder */
	define("SITE_ROOT", $_SERVER['DOCUMENT_ROOT']);	

	/** Define database configuration file */
	define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db.config.php');				
	
	/** Define max number of pages in pagination */
	define("MAX_ROWS_PER_PAGES", 8);
	define("MAX_ITEMS_TO_SHOW", 5); // Show 5 items per page in pagination buttons
?>
