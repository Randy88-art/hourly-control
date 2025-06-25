<?php

declare(strict_types=1);

namespace Application\Controller\tasks;

use Application\Core\Controller;
use Application\model\classes\Query;

final class TaskController extends Controller
{
    public function __construct(
        private Query $query = new Query(),        
    )
    {
        
    }

    public function index(): void
    {
        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            $tasks = $this->query->selectAll('tasks');

            $variables = [
                'menus'     => $this->showNavLinks(),
                'tasks'     => $tasks,
                'active'    => 'administration',
            ];

            $this->render('admin/tasks/index_view.twig', $variables);
            
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
    }
}
