<?php

declare(strict_types=1);

namespace Application\Controller\projects;

use Application\Core\Controller;
use Application\model\classes\Query;
use Application\model\classes\Validate;

class ProjectController extends Controller
{
    public function __construct(
        private Query $query = new Query(),
        private Validate $validate = new Validate(),
        private array $fields = [],
    )
    {
        
    }

    public function index(): void
    {
        global $id;

        try {
            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            // Set variables to pass to the view
            $variables = [
                'menus'      => $this->showNavLinks(),
                'session'    => $_SESSION,                
                'csrf_token' => $this->validate,                
                'active'     => 'administration',
            ];


            // Create pagination
            $currentPage = isset($id) ? (int) $id : 1;
            $limit = MAX_ROWS_PER_PAGES;
            $offset = ($currentPage - 1) * $limit;

            $totalProjects = $this->query->selectCount('projects'); // get total number of projects
            $totalPages = ceil($totalProjects / $limit); // calculate total number of pages            

            $projects = $this->query->selectRowsForPagination('projects', $limit, $offset);

            if($projects) {
                // New pagination variables to pass to the view
                $variables = array_merge($variables, [
                    'projects'       => $projects,
                    'currentPage'    => $currentPage,
                    'totalPages'     => $totalPages,
                    'totalProjects'  => $totalProjects,
                    'limit'          => $limit,
                    'maxPagesToShow' => MAX_ITEMS_TO_SHOW,
                ]);
            }

            $this->render('admin/projects/index_view.twig', $variables);

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
                    'Error:' =>  "Please contact the admin site. ",
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
            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            $variables = [
                'menus'      => $this->showNavLinks(),
                'session'    => $_SESSION,
                'csrf_token' => $this->validate,                
                'active'     => 'administration',
            ];

            // Check if the form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->fields = [
                    'project_name' => $this->validate->test_input($_POST['project_name']),                    
                    'active'       => isset($_POST['project_active']) ? 1 : 0, // Assuming 'active' is a checkbox
                ];

                // Validate CSRF token
                if ($this->validate->validate_csrf_token() && $this->validate->validate_form($this->fields)) {
                    // Insert the new project into the database
                    $this->query->insertInto('projects', $this->fields);
                    
                    // Redirect to the projects index page after saving
                    header('Location: /projects/project/index');

                } else {
                    // If validation fails, set an error message
                    $variables['error_message'] = !$this->validate->validate_form($this->fields) ?
                                                    $this->validate->get_msg() : 
                                                    'Invalid form submission. Invalid csrf_token.';

                    $variables['fields'] = $this->fields; // Keep the submitted data for repopulation
                    
                }
            }

            $this->render('admin/projects/new_project_view.twig', $variables);

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
                    'Error:' =>  'Please contact the admin site. ',
                ];
            }

            $this->render('error_view.twig', [
                'menus'             => $this->showNavLinks(),
                'exception_message' => $error_msg,                
            ]);
        }
    }

    public function edit() :void
    {
        try {
            global $id;

            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            // Get the current project from the database
            $this->fields = $this->query->selectOneBy('projects', 'project_id', $id);
             
            $variables = [
                'menus'      => $this->showNavLinks(),
                'session'    => $_SESSION,
                'fields'     => $this->fields,
                'csrf_token' => $this->validate,
                'active'     => 'administration',
            ];

            // Check if the form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->fields = [
                    'project_id'   => $id, // Assuming $id is the project ID being edited
                    'project_name' => $this->validate->test_input($_POST['project_name']),
                    'active'       => isset($_POST['project_active']) ? 1 : 0, // Checkbox handling
                ];

                // Validate CSRF token and form fields
                if ($this->validate->validate_csrf_token() && $this->validate->validate_form($this->fields)) {
                    // Check if the project exists
                    if (!$this->fields) {
                        throw new \Exception('Project not found');
                    }

                    // Update the project in the database
                    $this->query->updateRegistry('projects', $this->fields, 'project_id');

                    // Redirect to the projects index page after saving
                    header('Location: /projects/project/index');
                    
                } else {
                    // If validation fails, set an error message
                    $variables['error_message'] = !$this->validate->validate_form($this->fields) ? 
                                                    $this->validate->get_msg() :
                                                    'Invalid form submission. Invalid csrf_token.';
                    $variables['fields'] = $this->fields; // Keep the submitted data for repopulation
                }
            }

            $this->render('admin/projects/edit_project_view.twig', $variables);

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
                    'Error:' =>  'Please contact the admin site. ',
                ];
            }

            $this->render('error_view.twig', [
                'menus'             => $this->showNavLinks(),
                'exception_message' => $error_msg,                
            ]);
        }
    }

    public function delete(): void
    {
        try {
            global $id;

            // Test for privileges
            if(!$this->testAccess(['ROLE_ADMIN'])) throw new \Exception('Only admins can access this page');

            // Check if the project exists
            $project = $this->query->selectOneBy('projects', 'project_id', $id);
            if (!$project) {
                throw new \Exception('Project not found');
            }

            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                $_SESSION['csrf_token'] = $_POST['csrf_token'] ?? '';

                // Validate CSRF token
                if ($this->validate->validate_csrf_token()) {
                    // Delete the project from the database
                    $this->query->deleteRegistry('projects', 'project_id', $id);

                    // Redirect to the projects index page after deletion
                    header('Location: /projects/project/index');

                } else {
                    throw new \Exception('Invalid CSRF token');
                }
            }

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
                    'Error:' =>  "You cannot delete this project. ",
                ];
            }

            $this->render('error_view.twig', [
                'menus'             => $this->showNavLinks(),
                'exception_message' => $error_msg,                
            ]);
        }
    }                        
}