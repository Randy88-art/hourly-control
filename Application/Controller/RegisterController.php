<?php

    declare(strict_types = 1);

    namespace Application\Controller;

    use Application\Core\Controller;
    use Application\model\classes\Query;
    use Application\model\classes\Validate;

    class RegisterController extends Controller 
    {
        public function __construct(
            private Validate $validate,
            private Query $query,
            private array $fields = [],
            private string $message = "",            
        ) 
        {
            
        }

        /** Show register view */
        public function index(): void {

            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            $this->render('register/register_view.twig', [
                'menus'      =>  $this->showNavLinks(),
                'session'    =>  $_SESSION,
                'active'     =>  'registration',
                'csrf_token' => $this->validate,
            ]);
        }

        /** Register a new user */
        public function new(): void {                                                       
            if($_SERVER['REQUEST_METHOD'] == 'POST') {                     
                // Get values from register form                  
                $this->fields = [
                    'user_name' =>  $this->validate->test_input(strtolower($_POST['user_name'])),
                    'email'     =>  $this->validate->test_input(strtolower($_POST['email'])),
                    'password'  =>  $this->validate->test_input($_POST['password']),
                ];    
                
                $variables = [
                    'menus'      => $this->showNavLinks(),
                    'fields'     => $this->fields,
                    'active'     => 'registration',
                    'csrf_token' => $this->validate,   
                ];

                // Validate csrf token
                if(!$this->validate->validate_csrf_token()) {                        
                    $variables['error_message']   = "Invalid csrf token";
                    $variables['repeat_password'] = $this->validate->test_input($_POST['repeat_password']);                        
                }
                else {
                    // Test if the e-mail is in use by other user
                    $result = $this->query->selectOneBy('users', 'email', $this->fields['email']);                    

                    if($result) {
                        $variables['error_message'] = "The e-mail is already in use."; 
                        $variables['repeat_password'] = $this->validate->test_input($_POST['repeat_password']);                           
                    }
                    else {
                        // Test if passwords are equals
                        if(!empty($this->fields['password'])) {
                            if($this->fields['password'] !== $this->validate->test_input($_POST['repeat_password'])) {
                                $variables['error_message'] = "Passwords are not equals";
                                $variables['repeat_password'] = $this->validate->test_input($_POST['repeat_password']);  

                                $this->render('register/register_view.twig', $variables);                                  
                            }                                                                  
                        } 
                        
                        // Validate form                                                               
                        if($this->validate->validate_form($this->fields)) {                        
                            // Register the user
                            $this->query->insertInto('users', $this->fields);
                            $variables['message'] = "User registered successfully"; 
                            $variables['fields'] = [];                                  
                        }
                        else {                            
                            $variables['error_message'] = $this->validate->get_msg();
                            $variables['repeat_password'] = $this->validate->test_input($_POST['repeat_password']);                                
                        }
                    }
                } 
                
                $this->render('register/register_view.twig', $variables);
            }
            else {
                throw new \Exception("Service unavailable", 1);                    
            }                                                           
        }
    }  
?>