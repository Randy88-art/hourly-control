<?php

declare(strict_types = 1);

namespace Application\Controller\hourlycontrol;

use Application\Core\Controller;
use DateTime;
use Application\model\classes\QueryHourlyControl;
use Application\model\classes\Validate;
use Application\model\Project;
use Application\model\Task;

class HourlyController extends Controller
{
    public function __construct(
        private object $dbcon = DB_CON,
        private Validate $validate = new Validate(),
    )
    {
        
    }
  
    public function setInput()
    {        
        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_USER', 'ROLE_ADMIN'])) throw new \Exception('Only authorized users can access this page');

            // Test if there is already an input done without an output
            $queryHourlyControl = new QueryHourlyControl();
                       
            $dateIn = date('Y-m-d H:i:s');

            // Set necessary variables            
            $workstate       = $queryHourlyControl->getWorkState();
            $workstate_color = $queryHourlyControl->getWorkStateSuccessOrDanger();
            
            // We obtain the input, output hours and total time worked            
            $hours = $queryHourlyControl->getHours();

            // We obtain total time worked at day                                
            $hours = array_merge(
                $hours, 
                ['total_time' => $queryHourlyControl->getTotalTimeWorkedToday(date('Y-m-d'), $_SESSION['id_user'])]
            );  

            $variables = [
                'menus'             => $this->showNavLinks(),
                'session'           => $_SESSION,
                'workstate'         => $workstate,
                'workstate_color'   => $workstate_color, 
                'projects'          => $queryHourlyControl->selectAll('projects'),
                'tasks'             => $queryHourlyControl->selectAll('tasks'),
                'active'            => 'home',
                'csrf_token'        => $this->validate,
                'fields'            => [
                                        'project' => $queryHourlyControl->selectOneBy('projects', 'project_id', $this->validate->test_input($_POST['project'])) != false ? 
                                                        new Project($queryHourlyControl->selectOneBy('projects', 'project_id', $this->validate->test_input($_POST['project']))) : 
                                                        null,
                                        'task'    => $queryHourlyControl->selectOneBy('tasks', 'task_id', $this->validate->test_input($_POST['task'])) != false ? 
                                                        new Task($queryHourlyControl->selectOneBy('tasks', 'task_id', $this->validate->test_input($_POST['task']))) : 
                                                        null,
                                    ]
            ];            

            if($queryHourlyControl->isStartedTimeTrue($_SESSION['id_user'])) {
                $variables['error_message'] =  "Start time is already set";
                $variables['hours']         = $hours;                                                  
                $this->render('main_view.twig', $variables);
                die();                                                
            }                        
            
            // Validate csrf token
            if(!$this->validate->validate_csrf_token()) throw new \Exception("Invalid csrf token", 1);

            // Validate form
            if(!$this->validate->validate_form($variables['fields'])) {
                $variables['error_message'] = $this->validate->get_msg();
                $variables['hours']         = $hours;                        
                $this->render('main_view.twig', $variables);
                die();              
            }            
            else {
                $queryHourlyControl->insertInto("hourly_control", [
                    "id_user"    => $_SESSION['id_user'],
                    "date_in"    => $dateIn,
                    "project_id" => $variables['fields']['project']->getProjectId(),
                    "task_id"    => $variables['fields']['task']->getTaskId(),
                ]);
            }
                         
            header("Location: /");
            die();

        } catch (\Throwable $th) {
            $error_msg = [
                    'Error:' =>  $th->getMessage(),
            ];

            if($this->testAccess(['ROLE_ADMIN'])) {
                $error_msg = [
                    "Message:"  =>  $th->getMessage(),
                    "Path:"     =>  $th->getFile(),
                    "Line:"     =>  $th->getLine(),
                ];
            }

            $this->render('error_view.twig', [
                'menus'             => $this->showNavLinks(),
                'exception_message' => $error_msg,                
            ]);
        }
    }

    public function setOutput()
    {       
        $dateTime = new \DateTime('now');
        $dateOut = $dateTime->format('Y-m-d H:i:s');       

        $query = "UPDATE hourly_control 
                SET date_out = :date_out 
                WHERE id_user = :id_user 
                AND date_in = (SELECT MAX(date_in) 
                                FROM hourly_control
                                WHERE id_user = $_SESSION[id_user]) 
                AND date_out IS NULL";               
        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_USER', 'ROLE_ADMIN'])) throw new \Exception('Only authorized users can access this page');

            $stm = $this->dbcon->pdo->prepare($query);       
            $stm->bindValue(":date_out", $dateOut);
            $stm->bindValue(":id_user", $_SESSION['id_user']);
            $stm->execute();        

            header("Location: /");
            die();

        } catch (\Throwable $th) {
            $error_msg = [
                'Error:' =>  $th->getMessage(),
            ];

            if($this->testAccess(['ROLE_ADMIN'])) {
                $error_msg = [
                    "Message:"  =>  $th->getMessage(),
                    "Path:"     =>  $th->getFile(),
                    "Line:"     =>  $th->getLine(),
                ];
            }

            $this->render('error_view.twig', [
                'menus'             => $this->showNavLinks(),
                'exception_message' => $error_msg,                
            ]);
        }
    }

    public function setDuration(string $duration): void
    {
        $query = "UPDATE hourly_control 
                SET total_time_worked = :duration 
                WHERE id_user = :id_user 
                AND date_in = (SELECT MAX(date_in) 
                                FROM hourly_control
                                WHERE id_user = $_SESSION[id_user]) 
                AND date_out IS NOT NULL";               
        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_USER', 'ROLE_ADMIN'])) throw new \Exception('Only authorized users can access this page');
            
            $stm = $this->dbcon->pdo->prepare($query);       
            $stm->bindValue(":duration", $duration);
            $stm->bindValue(":id_user", $_SESSION['id_user']);
            $stm->execute();

        } catch (\Throwable $th) {
            $error_msg = [
                'Error:' =>  $th->getMessage(),
            ];

            if($this->testAccess(['ROLE_ADMIN'])) {
                $error_msg = [
                    "Message:"  =>  $th->getMessage(),
                    "Path:"     =>  $th->getFile(),
                    "Line:"     =>  $th->getLine(),
                ];
            }

            $this->render('error_view.twig', [
                'menus'             => $this->showNavLinks(),
                'exception_message' => $error_msg,                
            ]);
        }
    }
}