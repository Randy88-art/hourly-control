<?php

    declare(strict_types = 1);

    namespace Application\Controller;

    use Application\Core\Controller;
    use Application\Controller\hourlycontrol\HourlyController;
    use DateTime;
    use Application\model\classes\QueryHourlyControl;
    use Application\model\classes\Validate;

    class HomeController extends Controller
    {            

        public function __construct(
            private QueryHourlyControl $queryHourlyControl = new QueryHourlyControl, 
            private object $dbcon = DB_CON,
            private Validate $validate = new Validate()
        )        
        {                  
        }

        /**
         * If the user is working (date_out is null), it will set the 
         * workstate to 'Working'. Otherwise, it will set the
         * workstate to 'Not Working'.
         */
        public function index()
        { 
            try {
                // Initial options
                $options = [
                    'menus'  => $this->showNavLinks(),                     
                    'active' => 'home',                                 
                ];

                // If there is an active session test the working state
                // of the user and shows worked hours
                if(isset($_SESSION['id_user'])) {                    
                    $rows  = $this->queryHourlyControl->testWorkState();

                    $workstate       = ($rows && $rows['date_out'] === null && $rows['date_in'] !== null) ? 'Working' : 'Not Working';
                    $workstate_color = ($rows && $rows['date_out'] === null && $rows['date_in'] !== null) ? 'success' : 'danger';
                    
                    // We obtain the input, output hours and total time worked
                    $hours = [
                        'date_in'  => $rows['date_in']  ? date_format(new DateTime($rows['date_in']), 'H:i:s')  : '--:--:--',
                        'date_out' => $rows['date_out'] ? date_format(new DateTime($rows['date_out']), 'H:i:s') : '--:--:--',
                        'duration' => $rows['date_out'] ? date_diff(new DateTime($rows['date_in']), new DateTime($rows['date_out']))->format('%H:%I:%S') : '--:--:--',
                    ];

                    // Update duration in the DB
                    if($rows['date_out'] !== null) {
                        $hourlyController = new HourlyController();
                        $hourlyController->setDuration($hours['duration']);
                    }

                    // We obtain total time worked at day                    
                    $total_time_worked_at_day = $this->queryHourlyControl->getTotalTimeWorkedToday(date('Y-m-d'), $_SESSION['id_user']) ?? '--:--:--';
                    $hours = array_merge(
                        $hours, 
                        ['total_time' => $total_time_worked_at_day]
                    );

                    // Add new options to the lastest ones
                    $options = array_merge($options, [
                        'session'         => $_SESSION,
                        'workstate'       => $workstate,
                        'workstate_color' => $workstate_color,
                        'hours'           => $hours,
                        'projects'        => $this->queryHourlyControl->selectAll('projects'),
                        'tasks'           => $this->queryHourlyControl->selectAll('tasks'),
                        'csrf_token'      => $this->validate
                    ]);                    
                }
                                                                
                $this->render('main_view.twig', $options );

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
?>