<?php
declare(strict_types=1);

namespace Application\Core;


final class ErrorHandler
{
    public static function handle(\Throwable $th, $controllerInstance = null): void
    {
        $error_msg = [
            'Error' => $th->getMessage()
        ];

        if(isset($_SESSION['role']) && $_SESSION['role'] === 'ROLE_ADMIN') {
            $error_msg = [
                'Message'   => $th->getMessage(),
                'Path'      => $th->getFile(),
                'Line'      => $th->getLine(),
                'Trace'     => $th->getTraceAsString(),                
            ];
        }

        if($controllerInstance) {
            $controllerInstance->render('error_view.twig', [
                'menus' => $controllerInstance->showNavLinks(),
                'exception_message' => $error_msg,
            ]);

            exit();
        }

        // Si falló antes de crear el controlador, mostramos algo genérico
        echo "<h2>Critical Error</h2><pre>" . print_r($error_msg, true) . "</pre>";
        exit;
    }
}
?>