<?php

declare(strict_types=1);

namespace Application\Controller\admin;

use Application\Core\Controller;
use Application\model\classes\QueryHourlyControl;
use Application\model\classes\Validate;

final class SearchController extends Controller
{
    public function __construct(
        private Validate $validate = new Validate,
        private QueryHourlyControl $queryHourlyControl = new QueryHourlyControl(),
        private string $message = "",
    )
    {
        
    }
    public function searchByUserAndDate(): void
    {
        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            // Initialize variables
            $variables = [
                'menus'      => $this->showNavLinks(),
                'session'    => $_SESSION,
                'users'      => $this->queryHourlyControl->selectAll('users'),
                'csrf_token' => $this->validate,
                'active'     => 'administration'
            ];            
            
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $fields = [
                    'user' => $_POST['id_user'] != "" ? intval($this->validate->test_input($_POST['id_user'])) : "",
                    'date' => $this->validate->test_input($_POST['date'])            ?? "",
                ];

                if(!$this->validate->validate_csrf_token()) {
                    $variables['error_message'] = "Invalid token";                                     
                }                
                else if($this->validate->validate_form($fields)) {
                    // Add date to variables
                    $variables['date'] = $fields['date'];

                    $hours_by_user = [
                        'hours'      => $this->queryHourlyControl->getTotalTimeWorkedAtDayByUser($fields['date'], $fields['user']),
                        'total_time' => $this->queryHourlyControl->getTotalTimeWorkedToday($fields['date'], $fields['user']),
                    ];

                    // Add hours to variables
                    $variables = array_merge($variables, $hours_by_user);

                    // Add user to variables
                    $variables['user'] = $this->queryHourlyControl->selectOneBy('users', 'id', $fields['user']);

                    $this->render('admin/search_results.twig', $variables);
                    die;
                }
                else {
                    $variables['error_message'] = $this->validate->get_msg(); 
                    $variables['fields']        = $fields;                  
                }
            }            
            
            $this->render('admin/search/search_view.twig', $variables);

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

    public function searchByProjectName(): void
    {
        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            // Initialize variables
            $variables = [
                'menus'      => $this->showNavLinks(),
                'session'    => $_SESSION,                
                'csrf_token' => $this->validate,
                'active'     => 'administration'
            ];

            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $fields = [
                    'project_name' => isset($_POST['search_by_project_name']) ? $this->validate->test_input($_POST['search_by_project_name']) : "",
                ];                

                if(!$this->validate->validate_csrf_token()) {
                    $variables['error_message'] = "Invalid token";                                                      
                }
                else if($this->validate->validate_form($fields)) {
                    global $id;

                    // Create pagination
                    $currentPage = isset($id) ? (int) $id : 1;
                    $limit = MAX_ROWS_PER_PAGES;
                    $offset = ($currentPage - 1) * $limit;

                    $totalProjects = $this->queryHourlyControl->selectAllFromTableWhereFieldLike('projects', 'project_name', $fields['project_name']);
                    $totalPages = ceil(count($totalProjects) / $limit); // calculate total number of pages   

                    $projects = $this->queryHourlyControl->selectRowsForPaginationWhereFieldLikeValue('projects', $limit, $offset, 'project_name', $fields['project_name']);

                    if($projects) {
                        // New pagination variables to pass to the view
                        $variables = array_merge($variables, [
                            'projects'       => $projects,
                            'project_name'   => $fields['project_name'],
                            'currentPage'    => $currentPage,
                            'totalPages'     => $totalPages,
                            'totalProjects'  => $totalProjects,
                            'limit'          => $limit,
                            'maxPagesToShow' => MAX_ITEMS_TO_SHOW,
                        ]);
                    }

                    $this->render('admin/search/by_name/search_results_view.twig', $variables);
                    die;                    
                }
                else {
                    $variables['error_message'] = $this->validate->get_msg(); 
                    $variables['fields']        = $fields;                  
                }
            }
            
            $this->render('admin/search/by_name/search_by_project_name_view.twig', $variables);
            
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
