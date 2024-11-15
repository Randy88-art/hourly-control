<?php

declare(strict_types=1);

namespace Application\Controller\Admin;

use Application\Core\Controller;
use Application\model\classes\Query;
use Application\model\classes\QueryHourlyControl;
use Application\model\classes\Validate;

final class AdminController extends Controller
{
    public function index()
    {                        
        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            $query = new Query();
            $users = $query->selectAll('users');

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

        $this->render('admin/dashboard_view.twig', [
            'menus'   => $this->showNavLinks(),
            'session' => $_SESSION,
            'users'   => $users
        ]);
    }

    public function searchUserTimeWorked(): void
    {        
        try {
            $validate = new Validate();

            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            // Initialize variables
            $variables = [
                'menus' => $this->showNavLinks(),
                'session' => $_SESSION
            ];
            
            // Get values from search form
            if($_SERVER['REQUEST_METHOD'] != 'POST') throw new \Exception('Invalid request');

            $fields = [
                'user' => intval($validate->test_input($_POST['id_user'])) ?? "",
                'date' => $validate->test_input($_POST['date'])            ?? "",
            ];

            // Add date to variables
            $variables['date'] = $fields['date'];

            // Validate form
            if($validate->validate_form($fields)) {
                $queryHourlyControl = new QueryHourlyControl();                                    

                $hours_by_user = [
                    'hours'      => $queryHourlyControl->getTotalTimeWorkedAtDayByUser($fields['date'], $fields['user']),
                    'total_time' => $queryHourlyControl->getTotalTimeWorkedToday($fields['date'], $fields['user']),
                ];

                // Add hours to variables
                $variables = array_merge($variables, $hours_by_user);

                // Add user to variables
                $variables['user'] = $queryHourlyControl->selectOneBy('users', 'id', $fields['user']);

            }else{
                $query = new Query();
                $users = $query->selectAll('users');
                
                $this->render('admin/dashboard_view.twig', [
                    'menus'         => $this->showNavLinks(),
                    'session'       => $_SESSION,
                    'fields'        => $fields,
                    'error_message' => $validate->get_msg(),
                    'users'         => $users 
                ]);
            }

            $this->render('admin/search_results.twig', $variables);
            
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
