<?php

declare(strict_types=1);

use Application\Core\App;
use Application\Core\Controller;
use Application\model\classes\Query;
use Application\model\classes\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('TaskController')]
final class TaskControllerTest extends TestCase
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

    /**
     * Tests the index method of the TaskController class.
     * 
     * The index method should display all tasks.
     * 
     * @return void
     */
    public function testIndexTask(): void
    {
        # Set up
        $_SESSION['role']      = 'ROLE_ADMIN';
        $_SESSION['id_user']   = 1;
        $_SESSION['user_name'] = 'admin';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/tasks/task/index';                
       
        # Run logic        
        $tasks = $this->query->selectAll('tasks');

        ob_start();
        $this->app->router();
        $html = ob_get_contents();
        ob_end_clean();

        # Assertions
        $this->assertFileExists('Application/view/admin/tasks/index_view.twig');
        $this->assertIsArray($tasks);
        $this->assertStringContainsString('Tasks Index', $html);
    }

    public function testNewTask(): void
    {
        # Set up
        $_SESSION['role']          = 'ROLE_ADMIN';
        $_SESSION['id_user']       = 1;
        $_SESSION['user_name']     = 'admin';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/tasks/task/new';                
       
        # Run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN']); 
        $saved = false;
        
        // load the page
        ob_start();
        $this->app->router();
        $html = ob_get_contents();
        ob_end_clean();

        // submit the form
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/tasks/task/new';                
        $_POST['csrf_token']       = $_SESSION['csrf_token'] = $this->validate->csrf_token();
        $_POST['name']             = 'new task';       

        if($this->validate->validate_csrf_token() && $this->validate->validate_form(['task_name' => $_POST['name']])) {
            $this->query->insertInto('tasks', [
                'task_name' => $_POST['name'],
            ]);
            
            $saved = true;
        }
        
        # Assertions
        $this->assertFileExists('Application/view/admin/tasks/new_task_view.twig');
        $this->assertStringContainsString('New Task', $html);
        $this->assertTrue($saved);
    }
}
