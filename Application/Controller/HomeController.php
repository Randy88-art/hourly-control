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

                    $workstate       = $this->queryHourlyControl->getWorkState();
                    $workstate_color = $this->queryHourlyControl->getWorkStateSuccessOrDanger();
                    
                    // We obtain the input, output hours and total time worked
                    $hours = $this->queryHourlyControl->getHours();

                    // Update duration in the DB
                    if($rows['date_out'] !== null) {
                        $hourlyController = new HourlyController();
                        $hourlyController->setDuration($hours['duration']);
                    }

                    // We obtain total time worked at day                                
                    $hours = array_merge(
                        $hours, 
                        ['total_time' => $this->queryHourlyControl->getTotalTimeWorkedToday(date('Y-m-d'), $_SESSION['id_user'])]
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