<?php

declare(strict_types=1);

use Application\Core\App;
use Application\Core\Controller;
use Application\model\classes\QueryHourlyControl;
use Application\model\classes\Validate;
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
        $this->app        = new App();
        $this->validate   = new Validate;
        $this->controller = new Controller();
       
        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
	    define('DB_CON', $dbcon); 

        $this->queryHourlyControl = new QueryHourlyControl();
    }

    public function testSetInput() {      
        // set up
        $_SESSION['role']           = 'ROLE_ADMIN';
        $_SESSION['id_user']        = 1;
        $_SESSION['user_name']      = 'admin';
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $_SERVER['REQUEST_URI']     = '/';        

        // run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN', 'ROLE_USER']);
        $dateIn = date('Y-m-d H:i:s');
        $rows = $this->queryHourlyControl->testWorkState();
        $workstate       = $this->queryHourlyControl->getWorkState();
        $workstate_color = $this->queryHourlyControl->getWorkStateSuccessOrDanger();
        $hours = $this->queryHourlyControl->getHours();
        $total_time_worked_at_day = $this->queryHourlyControl->getTotalTimeWorkedToday(date('Y-m-d'), $_SESSION['id_user']);

        // assertions
        $this->assertEquals('true', $testAccess);
        $this->assertIsArray($rows);
        $this->assertContains($workstate, ['Working', 'Not Working']);
        $this->assertContains($workstate_color, ['success', 'danger']);
        $this->assertIsArray($hours);
        $this->assertArrayHasKey('date_in', $hours);
        $this->assertArrayHasKey('date_out', $hours);
        $this->assertArrayHasKey('duration', $hours);
    }
}
