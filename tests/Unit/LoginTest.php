<?php

declare(strict_types=1);

use Application\model\classes\Validate;
use Application\Core\App;
use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    protected App $app;
    private Validate $validate;

    public function setUp(): void
    {
        session_start();
	    session_regenerate_id();

        $this->app = new App();
        $this->validate = new Validate();

        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
	    define('DB_CON', $dbcon);  
    }

    // Test that the user is logged in
    public function testUserIsLoggedIn(): void
    {  
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/login';

        $_REQUEST['email']    = 'admin@admin.com';
        $_REQUEST['password'] = 'admin';
        $_POST['csrf_token']  = $_SESSION['csrf_token'] = $this->validate->csrf_token();                

        ob_start();

        $this->app->loadController();        

        $html = ob_get_contents();

        ob_end_clean();

        $expected = 'Logged as';

        $this->assertStringContainsString($expected, $html);
    }
}
