<?php

declare(strict_types=1);

use Application\Controller\HomeController;
use Application\Core\App;
use Application\model\classes\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HomeController::class)]
final class RoutingTest extends TestCase
{
    protected App $app;
    protected Validate $validate;

    public function setUp(): void
    {        
        $this->app = new App();
        $this->validate = new Validate();

        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
        define('DB_CON', $dbcon); 
    }


    /**
     * Tests if the homepage is rendered correctly by checking if the HTML output contains
     * the text 'Hours Control'.
     */
    public function testHomePageIsRendered(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/';

        ob_start();

        $this->app->router();

        $html = ob_get_contents();

        ob_end_clean();

        $this->assertStringContainsString('Hours Control', $html);       
    }

    /**
     * Tests if the login page is rendered correctly by checking if the HTML contains the
     * text 'Login Form'.
     */
    public function testLoginPageIsRendered(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/login';

        ob_start();

        $this->app->router();

        $html = ob_get_contents();

        ob_end_clean();

        $this->assertStringContainsString('Login Form', $html);       
    }

    /**
     * Tests if the register page is rendered after sending a login request and checking
     * if the user is logged in.
     */
    public function testRegisterPageIsRendered(): void
    {
        # Send login request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/login';

        $_REQUEST['email']    = 'admin@admin.com';
        $_REQUEST['password'] = 'admin';
        $_POST['csrf_token']  = $_SESSION['csrf_token'] = $this->validate->csrf_token();                        

        $this->app->router();
                
        # If user is logged in then show register view
        $this->app = new App();

        ob_start();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/register';       

        $this->app->router();

        $html = ob_get_contents();

        ob_end_clean();

        $this->assertStringContainsString('Register view', $html);       
    }
}
