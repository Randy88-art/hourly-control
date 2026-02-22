<?php
declare(strict_types=1);

namespace Application\Middlewares;

use Application\interfaces\MiddlewareInterface;

final class RoleMiddeleware implements MiddlewareInterface
{
    public function __construct(private array $roles)
    {}

    public function handle(): void
    {
        if(!isset($_SESSION['role'])) {
            $_SESSION['error_message'] = "Debes logearte para la acción requerida.";            
            
            header("Location: /");
        }
        
        if(isset($_SESSION['role']) && !in_array($_SESSION['role'], $this->roles)) {
            $_SESSION['error_message'] = "No tienes privilegios para realizar la acción.";

            header("Location: /");
        }
    }
}
