<?php

declare(strict_types=1);

namespace Application\Controller\tasks;

use Application\Core\Controller;
use Application\model\classes\Query;
use Application\model\classes\Validate;

final class TaskController extends Controller
{
    public function __construct(
        private Query $query = new Query(),
        private Validate $validate = new Validate,    
        private array $fields = [],        
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

    public function new(): void
    {
        try {
            $variables = [
                'menus'      => $this->showNavLinks(),
                'csrf_token' => $this->validate,
                'active'     => 'administration',
            ];

            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->fields  = [
                    'task_name' => $_POST['task_name'],                    
                ];

                if($this->validate->validate_csrf_token() && $this->validate->validate_form($this->fields)) {
                    $this->query->insertInto('tasks', [
                        'task_name' => $this->fields['task_name'],
                    ]);

                    header("Location: /tasks/task/index");
                }
                else {
                    $variables['fields']        = $this->fields;
                    $variables['error_message'] = !$this->validate->validate_form($this->fields) ? 
                                                    $this->validate->get_msg() : 
                                                    "Invalid csrf_token";
                }
            }

            $this->render('admin/tasks/new_task_view.twig', $variables);
            
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
