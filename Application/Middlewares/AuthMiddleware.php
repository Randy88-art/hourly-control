<?php
declare(strict_types=1);

namespace Application\Middlewares;

use Application\interfaces\MiddlewareInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'ROLE_ADMIN') {
            $_SESSION['error_message'] = "No tienes privilegios para realizar la acción.";            
            
            header("Location: /");
        }   
    }
}
