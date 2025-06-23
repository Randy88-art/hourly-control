<?php

declare(strict_types=1);

use Application\Core\App;
use Application\Core\Controller;
use Application\model\classes\QueryHourlyControl;
use Application\model\classes\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('SearchController')]
class SearchControllerTest extends TestCase
{
    protected ?App $app = null;
    protected ?Validate $validate = null;
    protected ?Controller $controller = null;
    protected ?QueryHourlyControl $queryHourlyControl = null;

    public function setUp(): void {
        define("SITE_ROOT", "/var/www/public");
        define('DB_CONFIG_FILE', SITE_ROOT . '/../Application/Core/db.config.php');
        require_once(SITE_ROOT . "/../Application/Core/connect.php");
	    define('DB_CON', $dbcon);

        $this->app = new App();
        $this->validate = new Validate();
        $this->controller = new Controller();
        $this->queryHourlyControl = new QueryHourlyControl();
    }

    public function testSearchByUserAndDataPageIsLoaded(): void
    {
        # Set up
        $_SESSION['role']           = 'ROLE_ADMIN';
        $_SESSION['id_user']        = 1;
        $_SESSION['user_name']      = 'admin';
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $_SERVER['REQUEST_URI']     = '/admin/search/searchByUserAndDate';
        $_SESSION['csrf_token']     = '1a39d5e2d509626cb8a5bce16bf0b160375a4683a07a99bbbda301c5dcf08703';
        $_POST['csrf_token']        = '1a39d5e2d509626cb8a5bce16bf0b160375a4683a07a99bbbda301c5dcf08703';

        # Run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN']);
        $users = $this->queryHourlyControl->selectAll('users');

        // load the page
        ob_start();
        $this->app->router();
        $html = ob_get_contents();
        ob_end_clean();

        # Assertions
        $this->assertTrue($testAccess);
        $this->assertFileExists('Application/view/admin/search/search_view.twig');
        $this->assertFileExists('Application/view/admin/search/form_search_by_user.twig');
        $this->assertStringContainsString('Search by user and date', $html);
        $this->assertIsArray($users);
    }

    public function testSendSearchByUserAndDataForm(): void
    {
        # Set up
        $_SESSION['role']           = 'ROLE_ADMIN';
        $_SESSION['id_user']        = 1;
        $_SESSION['user_name']      = 'admin';        
        $_SERVER['REQUEST_URI']     = '/admin/search/searchByUserAndDate';
        $_SESSION['csrf_token']     = '1a39d5e2d509626cb8a5bce16bf0b160375a4683a07a99bbbda301c5dcf08703';
        $_POST['csrf_token']        = '1a39d5e2d509626cb8a5bce16bf0b160375a4683a07a99bbbda301c5dcf08703';
        $_POST['id_user']           = '1';
        $_POST['date']              = '2025-06-23';
        $_SERVER['REQUEST_METHOD']  = 'POST';

        # Run logic
        $testAccess = $this->controller->testAccess(['ROLE_ADMIN']);
        $variables = [
            'menus'      => $this->controller->showNavLinks(),
            'session'    => $_SESSION,
            'users'      => $this->queryHourlyControl->selectAll('users'),
            'csrf_token' => $this->validate,
            'active'     => 'administration'
        ]; 

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $fields = [
                'user' => $_POST['id_user'] != "" ? intval($this->validate->test_input($_POST['id_user'])) : "",
                'date' => $this->validate->test_input($_POST['date']) ?? "",
            ];

            if(!$this->validate->validate_csrf_token()) {
                $variables['error_message'] = "Invalid token";                
            }
            else if($this->validate->validate_form($fields)) {
                // Add date to variables
                $variables['date'] = $fields['date'];

                $hours_by_user = [
                    'hours'      => $this->queryHourlyControl->getTotalTimeWorkedAtDayByUser($fields['date'], $fields['user']),
                    'total_time' => $this->queryHourlyControl->getTotalTimeWorkedToday($fields['date'], $fields['user']),
                ];

                // Add hours to variables
                $variables = array_merge($variables, $hours_by_user);

                // Add user to variables
                $variables['user'] = $this->queryHourlyControl->selectOneBy('users', 'id', $fields['user']);
                
                // Render the page
                $_SERVER['REQUEST_URI'] = '/admin/admin/searchUserTimeWorked';
                
                ob_start();                                                                        
                $this->app->router();       
                $html = ob_get_contents();
                ob_end_clean();                                                                   
            }
            else {
                $variables['error_message'] = $this->validate->get_msg(); 
                $variables['fields']        = $fields;                  
            }
        }                       

        # Assertions
        $this->assertTrue($testAccess);
        $this->assertTrue($this->validate->validate_csrf_token());
        $this->assertTrue($this->validate->validate_form($fields)); 
        $this->assertStringContainsString('Search Results', $html);
        $this->assertArrayHasKey('menus', $variables);
        $this->assertArrayHasKey('session', $variables);                 
        $this->assertArrayHasKey('users', $variables);
        $this->assertArrayHasKey('csrf_token', $variables);
        $this->assertArrayHasKey('active', $variables);
        $this->assertArrayHasKey('date', $variables);
        $this->assertArrayHasKey('hours', $variables);
        $this->assertArrayHasKey('total_time', $variables);
        $this->assertArrayHasKey('user', $variables);
        $this->assertEquals('/admin/admin/searchUserTimeWorked', $_SERVER['REQUEST_URI']);
    }
}
