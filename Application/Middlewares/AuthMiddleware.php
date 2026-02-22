<?php
declare(strict_types=1);

namespace Application\Middlewares;

use Application\interfaces\MiddlewareInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct()
    {}

    public function handle(): void
    {
        if(!isset($_SESSION['role'])) {
            $_SESSION['error_message'] = "Debes logearte para la acción requerida.";            
            
            header("Location: /");
        }                
    }
}