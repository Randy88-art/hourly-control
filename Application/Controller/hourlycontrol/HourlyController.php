<?php

declare(strict_types = 1);

namespace Application\Controller\hourlycontrol;

use App\Core\Controller;
use DateTime;
use model\classes\QueryHourlyControl;

class HourlyController extends Controller
{
    private object $dbcon = DB_CON;
    public function setInput()
    {        
        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_USER', 'ROLE_ADMIN'])) throw new \Exception('Only authorized users can access this page');

            // Test if there is already an input done without an output
            $queryHourlyControl = new QueryHourlyControl();
                       
            $dateIn = date('Y-m-d H:i:s');
            $query = new QueryHourlyControl();

            if(!$queryHourlyControl->isValidRow($_SESSION['id_user'])) {
                $rows  = $query->testWorkState();

                $workstate       = ($rows && $rows['date_out'] === null && $rows['date_in'] !== null) ? 'Working' : 'Not Working';
                $workstate_color = ($rows && $rows['date_out'] === null && $rows['date_in'] !== null) ? 'success' : 'danger';
                
                // We obtain the input, output hours and total time worked
                $hours = [
                    'date_in'  => $rows['date_in']  ? date_format(new DateTime($rows['date_in']), 'H:i:s')  : '--:--:--',
                    'date_out' => $rows['date_out'] ? date_format(new DateTime($rows['date_out']), 'H:i:s') : '--:--:--',
                    'duration' => $rows['date_out'] != null ? date_diff(new DateTime($rows['date_in']), new DateTime($rows['date_out']))->format('%H:%I:%S') : '--:--:--',
                ];

                // We obtain total time worked at day                    
                $total_time_worked_at_day = $query->getTotalTimeWorkedToday(date('Y-m-d'), $_SESSION['id_user']);
                $hours = array_merge(
                    $hours, 
                    ['total_time' => $total_time_worked_at_day]
                );                
                
                $this->render('main_view.twig', [
                        'menus' => $this->showNavLinks(),
                        'session' => $_SESSION,
                        'workstate'       => $workstate,
                        'workstate_color' => $workstate_color,
                        'hours'           => $hours,
                        'active' => 'home',
                        'error_message' => "Start time is already set",
                ]);                
            }

            $query->insertInto("hourly_control", [
                "id_user" => $_SESSION['id_user'],
                "date_in" => $dateIn
            ]); 
            
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