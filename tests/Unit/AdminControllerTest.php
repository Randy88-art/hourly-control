<?php

declare(strict_types=1);

use Application\Core\App;
use Application\Core\Controller;
use Application\model\classes\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('Application\Controller\Admin\AdminController')]
final class AdminControllerTest extends TestCase
{
    protected ?App $app = null;
    protected ?Validate $validate = null;
    protected ?Controller $controller = null;

    public function setUp(): void {
        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
	    define('DB_CON', $dbcon);

        $this->app = new App();
        $this->validate = new Validate();
        $this->controller = new Controller();
    }

    public function testDashboardIndexIsLoaded(): void
    {
        # Set up
        $_SESSION['role']       = 'ROLE_ADMIN';
        $_SESSION['id_user']    = 1;
        $_SESSION['user_name']  = 'admin';
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $_SERVER['REQUEST_URI']     = '/admin/admin/index';

        # Run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN']);

        // load the page
        ob_start();
        $this->app->router();        
        $html = ob_get_contents();
        ob_end_clean();

        # Assertions
        $this->assertTrue($testAccess);
        $this->assertFileExists('Application/view/admin/dashboard_view.twig');
        $this->assertStringContainsString('Dashboard Panel', $html);       
    }

    public function testSearchResultPageIsLoaded(): void {
        ob_start();

        # Send  request
        $_SESSION['role']          = 'ROLE_ADMIN';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/admin/admin/searchUserTimeWorked';
        $_POST['csrf_token']  = $_SESSION['csrf_token'] = $this->validate->csrf_token();

        $_POST['id_user']          = 1;
        $_POST['date']             = '2024-06-15';
               
        $this->app->router();       

        $html = ob_get_contents();

        ob_end_clean();

        $this->assertFileExists('Application/view/admin/search_results.twig');
        $this->assertStringContainsString('Search Results', $html);        
    }
}
