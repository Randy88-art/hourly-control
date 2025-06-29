<?php

declare(strict_types=1);

use Application\Controller\hourlycontrol\HourlyController;
use Application\Core\App;
use Application\Core\Controller;
use Application\model\classes\QueryHourlyControl;
use Application\model\classes\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('Application\Controller\HomeController')]
final class HomeControllerTest extends TestCase
{
    protected App $app;
    protected Validate $validate;
    protected QueryHourlyControl $queryHourlyControl;
    protected Controller $controller;

    public function setup(): void 
    {                             
        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db_test.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
	    define('DB_CON', $dbcon);
        
        $this->app = new App();
        $this->validate = new Validate;
        $this->queryHourlyControl = new QueryHourlyControl();
        $this->controller = new Controller();
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
        $_SESSION['csrf_token']     = '1a39d5e2d509626cb8a5bce16bf0b160375a4683a07a99bbbda301c5dcf08703';
        $_POST['csrf_token']        = '1a39d5e2d509626cb8a5bce16bf0b160375a4683a07a99bbbda301c5dcf08703';                

        # Run the logic
        // Initial options
        $options = [
            'menus'  => $this->controller->showNavLinks(),                     
            'active' => 'home',                                 
        ];

        $rows            = $this->queryHourlyControl->testWorkState();
        $workstate       = $this->queryHourlyControl->getWorkState();
        $workstate_color = $this->queryHourlyControl->getWorkStateSuccessOrDanger();
        $hours           = $this->queryHourlyControl->getHours();

        // Update duration in the DB
        if($rows['date_out'] !== null) {
            $hourlyController = new HourlyController();
            $hourlyController->setDuration($hours['duration']);
        }

        $hours = array_merge(
            $hours, 
            ['total_time' => $this->queryHourlyControl->getTotalTimeWorkedToday(date('Y-m-d'), $_SESSION['id_user'])]
        );

        $options = array_merge($options, [
            'session'         => $_SESSION,
            'workstate'       => $workstate,
            'workstate_color' => $workstate_color,
            'hours'           => $hours,
            'projects'        => $this->queryHourlyControl->selectAll('projects'),
            'tasks'           => $this->queryHourlyControl->selectAll('tasks'),
            'csrf_token'      => $this->validate
        ]); 
        
        $this->app->router();
        
        $html = ob_get_contents();

        ob_end_clean();

        # Add assertions
        $this->assertFileExists('Application/view/main_view.twig');
        $this->assertStringContainsString('Select project and task', $html);
        $this->assertArrayHasKey('menus', $options);
        $this->assertArrayHasKey('session', $options);
        $this->assertArrayHasKey('workstate', $options);
        $this->assertArrayHasKey('workstate_color', $options);
        $this->assertArrayHasKey('active', $options);
        $this->assertContains($workstate, ['Working', 'Not Working']);
        $this->assertContains($workstate_color, ['success', 'danger']);
        $this->assertIsArray($hours);
        $this->assertArrayHasKey('projects', $options);
        $this->assertArrayHasKey('tasks', $options);
        $this->assertArrayHasKey('date_in', $hours);
        $this->assertArrayHasKey('date_out', $hours);
        $this->assertArrayHasKey('duration', $hours);
        $this->assertArrayHasKey('total_time', $hours);
        $this->assertArrayHasKey('csrf_token', $options);
    }
}
