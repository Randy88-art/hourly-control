<?php

declare(strict_types=1);

use Application\Core\App;
use Application\model\classes\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('Application\Controller\Admin\AdminController')]
final class AdminControllerTest extends TestCase
{
    protected App $app;
    protected Validate $validate;

    public function setUp(): void {
        $this->app = new App();
        $this->validate = new Validate();

        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
	    define('DB_CON', $dbcon); 
    }

    public function testSearchResultPageIsLoaded(): void {                        
        # Send  request
        $_SESSION['role']          = 'ROLE_ADMIN';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/admin/admin/searchUserTimeWorked';
        $_POST['csrf_token']  = $_SESSION['csrf_token'] = $this->validate->csrf_token();

        $_POST['id_user']          = 1;
        $_POST['date']             = '2022-01-01';
        
        ob_start();

        $this->app->router();        

        $html = ob_get_contents();

        ob_end_clean();

        $this->assertStringContainsString('Search Results', $html);        
    }
}
