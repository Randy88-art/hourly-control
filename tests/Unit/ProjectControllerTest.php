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
        define('MAX_PAGES', 10);
        define('MAX_ROWS_PER_PAGES', 8);
        define('MAX_ITEMS_TO_SHOW', 5);

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
        $projects = $this->query->selectRowsForPagination('projects', MAX_PAGES, 1);

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

    public function testNewProject(): void
    {
        // Setup
        $_SESSION['role']          = 'ROLE_ADMIN';
        $_SESSION['id_user']       = 1;
        $_SESSION['user_name']     = 'admin';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/projects/project/new';        

        // Run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN']);        

        // Capture output
        ob_start();
        $this->app->router();
        $html = ob_get_contents();
        ob_end_clean();

        // Simulate form submission
        $_POST['csrf_token'] = $this->validate->csrf_token();

        // Save the new project (this is a mock, in real case it would be saved to the database)
        $saved = false;

        // Simulate form submission
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/projects/project/new';
        $_POST['project_name'] = 'New Project';
        $_POST['project_description'] = 'New Project Description';
        $_POST['active'] = 1; // Assuming 'active' is a checkbox or similar input
        
        $fields = [
            'project_name'        => $this->validate->test_input($_POST['project_name']),
            'project_description' => $this->validate->test_input($_POST['project_description']),
            'active'              => $this->validate->test_input($_POST['active']),
        ];

        // Validate CSRF token and form fields
        if ($this->validate->validate_csrf_token() && $this->validate->validate_form($fields)) {
            $this->query->insertInto('projects', $fields);
            $saved = true; // Simulate saving the project
        }

        // Assert
        $this->assertTrue($testAccess, 'Access should be granted for admin role');
        $this->assertFileExists('Application/view/admin/projects/new_project_view.twig', 'New view file should exist');
        $this->assertStringContainsString('New Project', $html, 'HTML should contain "New Project"');
        $this->assertTrue($saved, 'Project should be saved successfully');
    }

    public function testEditProject(): void
    {
        // Setup        
        $_SESSION['role']          = 'ROLE_ADMIN';
        $_SESSION['id_user']       = 1;
        $_SESSION['user_name']     = 'admin';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/projects/project/edit/4';        
        
        global $id;           

        // Run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN']);
                
        // Capture output
        ob_start();
        $this->app->router();        
        $html = ob_get_contents();
        ob_end_clean();

        $project = $this->query->selectOneBy('projects', 'project_id', $id);
                
        // Simulate form submission
        $_SERVER['REQUEST_METHOD']    = 'POST';
        $_SERVER['REQUEST_URI']       = '/projects/project/edit/' . $id;
        $_POST['csrf_token']          = $this->validate->csrf_token(); // Simulating CSRF token for the test
        $_POST['project_name']        = 'Updated Project';
        $_POST['project_description'] = 'Updated Project Description';
        $_POST['active']              = 1; // Assuming 'active' is a checkbox or similar input        

        if($_SERVER['REQUEST_METHOD'] === 'POST') { 
            // Prepare fields for update
            $fields = [
                'project_id'          => $id, // Assuming $id is the project ID being edited
                'project_name'        => $this->validate->test_input($_POST['project_name']),
                'project_description' => $this->validate->test_input($_POST['project_description']),
                'active'              => $this->validate->test_input($_POST['active']),
            ];

            // Check if the project exists
            if (!$project) {
                throw new \Exception('Project not found');
            }

            // Validate CSRF token and form fields            
            if ($this->validate->validate_csrf_token() && $this->validate->validate_form($fields)) {
                // Simulate updating the project in the database
                $this->query->updateRegistry('projects', $fields, 'project_id');
                $updated = true; // Simulate successful update
            } else {
                $updated = false; // Simulate failed update
            }
        }
        
        // Assert
        $this->assertTrue($testAccess, 'Access should be granted for admin role');
        $this->assertFileExists('Application/view/admin/projects/edit_project_view.twig', 'Edit view file should exist');
        $this->assertStringContainsString('Edit Project', $html, 'HTML should contain "Edit Project"');
        $this->assertIsArray($project, 'Project should be an array');
        $this->assertTrue($updated, 'Project should be updated successfully');
    }

    public function testDeleteProject(): void
    {
        // Setup
        $_SESSION['role']          = 'ROLE_ADMIN';
        $_SESSION['id_user']       = 1;
        $_SESSION['user_name']     = 'admin';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/projects/project/delete/3'; // Assuming project ID 3 exists        

        global $id;
        $deleted = false;       

        // Run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN']);

        // Capture output
        ob_start();
        $this->app->router();        
        $html = ob_get_contents();
        ob_end_clean();

        // Get the project to be deleted
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/projects/project/delete/' . $id;
        $_POST['csrf_token']       = $this->validate->csrf_token(); // Simulating CSRF token for the test

        // Simulate deletion
        if($_SERVER['REQUEST_METHOD'] === 'POST' && $this->validate->validate_csrf_token()) {                                    
            try {
                $this->query->deleteRegistry('projects', 'project_id', $id);
                $deleted = true; // Simulate successful deletion
            } catch (\Throwable $th) {
                $message = $th->getMessage();
            }
        }              

        // Assert
        $this->assertTrue($testAccess, 'Access should be granted for admin role');
        $this->assertFalse($deleted, 'Project should not be deleted successfully'); // Assuming deletion is not allowed in this test
        $this->assertStringContainsString('Cannot delete or update a parent row: a foreign key constraint fails ', $message);
    }
}