<?php

    declare(strict_types = 1);

    namespace Application\Controller;

    use App\Core\Controller;
    use model\classes\Query;
    use model\classes\Validate;

    class LoginController extends Controller
    {        
        public function __construct(private object $dbcon = DB_CON)
        {

        }

        public function index(): void
        { 
            $validate = new Validate;
            $query = new Query;

            try {
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Get values from login form
                    $fields = [
                        'email'     =>  $validate->validate_email(strtolower($_REQUEST['email'])) ? $validate->test_input(strtolower($_REQUEST['email'])) : "",
                        'password'  =>  $validate->test_input($_REQUEST['password']) ?? "",
                    ];

                    // Validate form                    
                    if($validate->validate_form($fields)) {
                        if(!isset($_SESSION['id_user'])) {
                            // Test user to do login                           
                            $result = $query->selectLoginUser('users', 'roles', 'id_role', $fields['email'], $this->dbcon);                                                       
                                                        
                            if($result) {                                
                                if(password_verify($fields['password'], $result['password'])) {												
                                    $_SESSION['id_user']    = $result['id'];						
                                    $_SESSION['user_name']  = $result['user_name'];
                                    $_SESSION['role']       = $result['role'];												
                                                                                                       
                                    header("Location: /home");
                                    die();						
                                }
                                else {
                                    $this->render('login/login_view.twig', [
                                        'menus'         =>  $this->showNavLinks(), 
                                        'error_message' =>  'Please test your credentials', 
                                        'fields'        =>  $fields,
                                        'active'        =>  'login',                   
                                    ]);
                                }                                                                
                            }
                            else {
                                $this->render('login/login_view.twig', [
                                    'menus'         =>  $this->showNavLinks(), 
                                    'error_message' =>  'Please test your credentials',
                                    'fields'        =>  $fields, 
                                    'active'        =>  'login',                
                                ]);
                            }                            
                        }
                        else {
                            header("Location: /home");
                            die();	
                        }
                    }
                }                                                                             

                $this->render('login/login_view.twig', [
                    'menus'     => $this->showNavLinks(),
                    'active'    =>  'login',                
                ]);

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