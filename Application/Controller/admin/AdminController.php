<?php

declare(strict_types=1);

namespace Application\Controller\Admin;

use Application\Core\Controller;
use Application\model\classes\Query;
use Application\model\classes\Validate;

final class AdminController extends Controller
{
    public function __construct(
        private Validate $validate,
        private Query $query,        
        private string $message = "",
    )
    {        
    }

    public function index()
    {
        $this->render('admin/dashboard_view.twig', [
            'menus'   => $this->showNavLinks(),
            'session' => $_SESSION,
            'active'  => 'administration'            
        ]);
    }   
}
