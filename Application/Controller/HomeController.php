<?php

    declare(strict_types = 1);

    namespace Application\Controller;

    use App\Core\Controller;
    use PDO;

    class HomeController extends Controller
    {        
        public function __construct(private object $dbcon = DB_CON)
        {

        }

        /**
         * If the user is working (date_out is null), it will set the 
         * workstate to 'Working' and the color to 'success'. Otherwise, 
         * it will set the workstate to 'Not Working' and the color to 
         * 'danger'.
         */
        public function index()
        { 
            try {
                $options = [
                    'menus'  => $this->showNavLinks(),                     
                    'active' => 'home',                                 
                ];

                // If there is an active session
                if(isset($_SESSION['id_user'])) {
                    $query = "SELECT date_in, date_out FROM hourly_control 
                    WHERE id_user = :id_user 
                    AND date_in = (SELECT MAX(date_in) FROM hourly_control)";
                    
                    $stm = $this->dbcon->pdo->prepare($query);
                    $stm->bindValue(":id_user", $_SESSION['id_user']);
                    $stm->execute();

                    $rows = $stm->fetch(PDO::FETCH_ASSOC);

                    $workstate       = ($rows && $rows['date_out'] === null && $rows['date_in'] !== null) ? 'Working' : 'Not Working';
                    $workstate_color = ($rows && $rows['date_out'] === null && $rows['date_in'] !== null) ? 'success' : 'danger';

                    $options = array_merge($options, [
                        'session'         => $_SESSION,
                        'workstate'       => $workstate,
                        'workstate_color' => $workstate_color,
                    ]);                    
                }
                                                                
                $this->render('main_view.twig', $options );

            } catch (\Throwable $th) {
                $error_msg = [
                    'error' =>  $th->getMessage(),
                ];

                if(isset($_SESSION['role']) && $_SESSION['role'] === 'ROLE_ADMIN') {
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