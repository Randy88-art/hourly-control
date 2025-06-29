<?php

declare(strict_types=1);

use Application\Core\App;
use Application\Core\Controller;
use Application\model\classes\QueryHourlyControl;
use Application\model\classes\Validate;
use Application\model\Project;
use Application\model\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('Application\Controller\HourlyController')]
final class HourlyControllerTest extends TestCase
{
    protected App $app;
    protected Validate $validate;
    protected Controller $controller;
    protected QueryHourlyControl $queryHourlyControl;

    public function setup(): void      
    {
        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db_test.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
	    define('DB_CON', $dbcon);

        $this->app        = new App();
        $this->validate   = new Validate;
        $this->controller = new Controller();                
        $this->queryHourlyControl = new QueryHourlyControl();
    }

    public function testSetInput() {      
        // Set up
        $_SESSION['role']           = 'ROLE_ADMIN';
        $_SESSION['id_user']        = 1;
        $_SESSION['user_name']      = 'admin';        
        $_POST['project']           = '3';
        $_POST['task']              = '6';
        $_SESSION['csrf_token']     = '1a39d5e2d509626cb8a5bce16bf0b160375a4683a07a99bbbda301c5dcf08703';
        $_POST['csrf_token']        = '1a39d5e2d509626cb8a5bce16bf0b160375a4683a07a99bbbda301c5dcf08703';

        // Run logic
        $testAccess      = $this->controller->testAccess(['ROLE_ADMIN', 'ROLE_USER']);
        $dateIn          = date('Y-m-d H:i:s');
        $rows            = $this->queryHourlyControl->testWorkState();
        $workstate       = $this->queryHourlyControl->getWorkState();
        $workstate_color = $this->queryHourlyControl->getWorkStateSuccessOrDanger();
        $hours           = $this->queryHourlyControl->getHours();        

        $hours = array_merge(
            $hours, 
            ['total_time' => $this->queryHourlyControl->getTotalTimeWorkedToday(date('Y-m-d'), $_SESSION['id_user'])]
        );
        
        $variables = [
            'menus'             => $this->controller->showNavLinks(),
            'session'           => $_SESSION,
            'workstate'         => $workstate,
            'workstate_color'   => $workstate_color, 
            'projects'          => $this->queryHourlyControl->selectAll('projects'),
            'tasks'             => $this->queryHourlyControl->selectAll('tasks'),
            'active'            => 'home',
            'csrf_token'        => $this->validate,
            'fields'            => [
                                    'project' => $this->queryHourlyControl->selectOneBy('projects', 'project_id', $this->validate->test_input($_POST['project'])) != false ? 
                                                    new Project($this->queryHourlyControl->selectOneBy('projects', 'project_id', $this->validate->test_input($_POST['project']))) : 
                                                    null,
                                    'task'    => $this->queryHourlyControl->selectOneBy('tasks', 'task_id', $this->validate->test_input($_POST['task'])) != false ? 
                                                    new Task($this->queryHourlyControl->selectOneBy('tasks', 'task_id', $this->validate->test_input($_POST['task']))) : 
                                                    null,
                                ]
        ];         

        if($this->queryHourlyControl->isStartedTimeTrue($_SESSION['id_user'])) {
            $variables['error_message'] =  "Start time is already set";
            $variables['hours']         = $hours;
            
            $this->assertArrayHasKey('error_message', $variables);
            $this->assertArrayHasKey('hours', $variables);            
        }
        
        $csrf_token_validation_message = $this->validate->validate_csrf_token();
        
        if(!$this->validate->validate_form($variables['fields'])) {
            $variables['error_message'] = $this->validate->get_msg();
            $variables['hours']         = $hours;                                 
            
            $this->assertArrayHasKey('error_message', $variables);
            $this->assertArrayHasKey('hours', $variables);           
        }
        else {
            $this->queryHourlyControl->insertInto("hourly_control", [
                "id_user"    => $_SESSION['id_user'],
                "date_in"    => $dateIn,
                "project_id" => $variables['fields']['project']->getProjectId(),
                "task_id"    => $variables['fields']['task']->getTaskId(),
            ]);
        }

        // Assertions
        $this->assertEquals('true', $testAccess);
        $this->assertIsArray($rows);
        $this->assertContains($workstate, ['Working', 'Not Working']);
        $this->assertContains($workstate_color, ['success', 'danger']);
        $this->assertIsArray($hours);
        $this->assertArrayHasKey('date_in', $hours);
        $this->assertArrayHasKey('date_out', $hours);
        $this->assertArrayHasKey('duration', $hours);
        $this->assertArrayHasKey('total_time', $hours);
        $this->assertArrayHasKey('menus', $variables);
        $this->assertArrayHasKey('session', $variables);
        $this->assertArrayHasKey('workstate', $variables);
        $this->assertArrayHasKey('workstate_color', $variables);
        $this->assertArrayHasKey('projects', $variables);
        $this->assertArrayHasKey('tasks', $variables);
        $this->assertArrayHasKey('active', $variables);
        $this->assertArrayHasKey('csrf_token', $variables);
        $this->assertArrayHasKey('project', $variables['fields']);
        $this->assertArrayHasKey('task', $variables['fields']);
        $this->assertEquals('true', $csrf_token_validation_message);
        $this->assertArrayHasKey('fields', $variables);
        $this->assertIsArray($variables['fields']);
    }

    public function testSetOutput() {
        # Set up
        $_SESSION['role']           = 'ROLE_ADMIN';
        $_SESSION['id_user']        = 1;
        $_SESSION['user_name']      = 'admin';        

        # Run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN', 'ROLE_USER']);
        $dateTime   = new \DateTime('now');
        $dateOut    = $dateTime->format('Y-m-d H:i:s');

        $this->queryHourlyControl->setOutput($dateOut);

        # Assertions
        $this->assertEquals('true', $testAccess);
        $this->assertIsString($dateOut);
    }
}
