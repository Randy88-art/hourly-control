<?php

declare(strict_types=1);

use Application\Core\App;
use Application\model\classes\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('Application\Controller\HomeController')]
final class HomeControllerTest extends TestCase
{
    protected App $app;
    protected Validate $validate;

    public function setup(): void 
    {
        $this->app = new App();
        $this->validate = new Validate;

        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
	    define('DB_CON', $dbcon); 
    }

    public function testHomePageIsLoaded(): void
    {
        ob_start();

        # Set up variables
        $_SESSION['role']           = 'ROLE_ADMIN';
        $_SESSION['id_user']        = 1;
        $_SESSION['user_name']      = 'admin';
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $_SERVER['REQUEST_URI']     = '/';        

        # Run the app
        $this->app->router();
        
        $html = ob_get_contents();

        ob_end_clean();

        # Add assertions
        $this->assertFileExists('Application/view/main_view.twig');
        $this->assertStringContainsString('Select project and task', $html);
    }
}
