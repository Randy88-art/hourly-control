<?php

    declare(strict_types = 1);

    namespace Application\Controller;

    use Application\Core\Controller;
    use Application\model\classes\Query;
    use Application\model\classes\Validate;

    class RegisterController extends Controller 
    {
        public function __construct(
            private array $fields = [],
            private string $message = "",
            private Validate $validate = new Validate
        ) 
        {
            
        }

        /** Show register view */
        public function index(): void {

            try {
                // Test for privileges
                if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

                $this->render('register/register_view.twig', [
                    'menus'         =>  $this->showNavLinks(),
                    'session'       =>  $_SESSION,
                    'active'        =>  'registration',
                    'csrf_token'    => $this->validate,
                ]);

            } catch (\Throwable $th) {
                $error_msg = [
                    'Error:' =>  $th->getMessage(),
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

        /** Register a new user */
        public function new(): void {            
            $query = new Query;             
                        
            try {                                
                if($_SERVER['REQUEST_METHOD'] == 'POST') { 
                    
                    // Get values from register form                  
                    $this->fields = [
                        'user_name' =>  $this->validate->test_input(strtolower($_REQUEST['user_name'])),
                        'email'     =>  $this->validate->test_input(strtolower($_REQUEST['email'])),
                        'password'  =>  $this->validate->test_input($_REQUEST['password']),
                    ];    
                    
                    $variables = [
                        'menus'         => $this->showNavLinks(),
                        'fields'        => $this->fields,
                        'active'        => 'registration',
                        'csrf_token'    => $this->validate,   
                    ];

                    // Validate csrf token
                    if(!$this->validate->validate_csrf_token()) {                        
                        $variables['error_message']   = "Invalid csrf token";
                        $variables['repeat_password'] = $this->validate->test_input($_REQUEST['repeat_password']);                        
                    }
                    else {
                        // Test if the e-mail is in use by other user
                        $result = $query->selectOneBy('users', 'email', $this->fields['email']);                    

                        if($result) {
                            $variables['error_message'] = "The e-mail is already in use."; 
                            $variables['repeat_password'] = $this->validate->test_input($_REQUEST['repeat_password']);                           
                        }
                        else {
                            // Test if passwords are equals
                            if(!empty($this->fields['password'])) {
                                if($this->fields['password'] !== $this->validate->test_input($_REQUEST['repeat_password'])) {
                                    $variables['error_message'] = "Passwords are not equals";
                                    $variables['repeat_password'] = $this->validate->test_input($_REQUEST['repeat_password']);  

                                    $this->render('register/register_view.twig', $variables);                                  
                                }                                                                  
                            } 
                            
                            // Validate form                                                               
                            if($this->validate->validate_form($this->fields)) {                        
                                // Register the user
                                $query->insertInto('users', $this->fields);
                                $variables['message'] = "User registered successfully"; 
                                $variables['fields'] = [];                                  
                            }
                            else {                            
                                $variables['error_message'] = $this->validate->get_msg();
                                $variables['repeat_password'] = $this->validate->test_input($_REQUEST['repeat_password']);                                
                            }
                        }
                    } 
                    
                    $this->render('register/register_view.twig', $variables);
                }
                else {
                    throw new \Exception("Service unavailable", 1);                    
                }  
                                              
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