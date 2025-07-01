<?php

declare(strict_types=1);

use Application\Core\App;
use Application\Core\Controller;
use Application\model\classes\Query;
use Application\model\classes\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('ProjectController')]
final class ProjectControllerTest extends TestCase
{
    protected ?App $app               = null;
    protected ?Validate $validate     = null;
    protected ?Controller $controller = null;
    protected ?Query $query           = null;

    public function setup(): void {
        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db_test.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
	    define('DB_CON', $dbcon);

        $this->app        = new App();
        $this->validate   = new Validate();
        $this->controller = new Controller();
        $this->query      = new Query();
    }
    public function testIndexProjects(): void
    {
        // Setup
        $_SESSION['role']          = 'ROLE_ADMIN';
        $_SESSION['id_user']       = 1;
        $_SESSION['user_name']     = 'admin';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/projects/project/index';

        // Run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN']);
        $projects = $this->query->selectAll('projects');

        // Capture output
        ob_start();
        $this->app->router();
        $html = ob_get_contents();
        ob_end_clean();

        // Assert
        $this->assertTrue($testAccess, 'Access should be granted for admin role');
        $this->assertFileExists('Application/view/admin/projects/index_view.twig', 'Index view file should exist');
        $this->assertIsArray($projects, 'Projects should be an array');
        $this->assertStringContainsString('Projects Index', $html, 'HTML should contain "Projects Index"');
    }
}