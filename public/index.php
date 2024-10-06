<?php
	declare(strict_types=1);
	
	use Core\App;
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/../Application/Core/aplication_fns.php");
	
	Application\model\classes\Loader::init($_SERVER['DOCUMENT_ROOT'] . "/..");
	
	$app = new App;
	$app->loadController();	
?>
