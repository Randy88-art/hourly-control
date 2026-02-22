<?php

    declare(strict_types = 1);

    namespace Application\Controller;

    use Application\Core\Controller;
    use Application\model\classes\Query;
    use Application\model\classes\Validate;

    class LoginController extends Controller
    {        
        public function __construct(                        
            private Validate $validate,
            private Query $query,
            private string $message = "",
            private array $fields = [],
        )
        {            
        }

        public function index(): void
        {
            $twig_variables = [
                'menus'         => $this->showNavLinks(),                    
                'active'        => 'login',
                'csrf_token'    => $this->validate,              
            ];

            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    
                // Get values from login form
                $this->fields = [
                    'email'     =>  $this->validate->validate_email(strtolower($_POST['email'])) ? $this->validate->test_input(strtolower($_POST['email'])) : "",
                    'password'  =>  $this->validate->test_input($_POST['password']) ?? "",
                ];                

                $twig_variables = array_merge($twig_variables, ['fields' => $this->fields]);

                // Validate csrf token
                if(!$this->validate->validate_csrf_token()) {
                    $this->message = "Invalid csrf token";
                    $twig_variables['error_message'] = $this->message;
                }
                else {
                    // Validate form                    
                    if($this->validate->validate_form($this->fields)) {
                        if(!isset($_SESSION['id_user'])) {
                            // Test user to do login                           
                            $result = $this->query->selectLoginUser('users', 'roles', 'id_role', $this->fields['email']);                                                       
                                                        
                            if($result) {                                
                                if(password_verify($this->fields['password'], $result['password'])) {
                                    session_regenerate_id();

                                    $_SESSION['id_user']    = $result['id'];						
                                    $_SESSION['user_name']  = $result['user_name'];
                                    $_SESSION['role']       = $result['role'];												
                                                                                                    
                                    header("Location: /home");                                      
                                    return;						
                                }
                                else {
                                    $twig_variables['error_message'] = 'Please test your credentials';
                                }                                                                
                            }
                            else {
                                $twig_variables['error_message'] = 'Please test your credentials';
                            }                            
                        }
                        else {
                            header("Location: /home");                            
                        }
                    }
                }                               
            }                                                                             

            $this->render('login/login_view.twig', $twig_variables);                                           
        }        
    }    
?>
