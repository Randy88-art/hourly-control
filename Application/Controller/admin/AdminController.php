<?php

declare(strict_types=1);

namespace Application\Controller\Admin;

use Application\Core\Controller;
use Application\model\classes\Query;
use Application\model\classes\Validate;

final class AdminController extends Controller
{
    public function __construct(
        private Query $query = new Query(),
        private Validate $validate = new Validate,
        private string $message = "",
    )
    {        
    }

    public function index()
    {                        
        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');                    

        } catch (\Throwable $th) {            
            if($this->testAccess(['ROLE_ADMIN'])) {
                $error_msg = [
                    "Message:"  =>  $th->getMessage(),
                    "Path:"     =>  $th->getFile(),
                    "Line:"     =>  $th->getLine(),
                ];
            }
            else {
                $error_msg = [
                    'Error:' =>  $th->getMessage(),
                ];
            }

            $this->render('error_view.twig', [
                'menus'             => $this->showNavLinks(),
                'exception_message' => $error_msg,                
            ]);
        }

        $this->render('admin/dashboard_view.twig', [
            'menus'   => $this->showNavLinks(),
            'session' => $_SESSION,
            'active'  => 'administration'            
        ]);
    }   
}
