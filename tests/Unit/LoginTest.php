<?php

declare(strict_types=1);

namespace Tests\Unit;

use Application\Controller\LoginController;
use Application\Database\Connection;
use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    public function testLogin(): void
    {        
        $dbcon = new Connection(include_once(__DIR__ . '/../../Application/Core/db.config.php'));        

        define('DB_CON', $dbcon);

        $loginController = new LoginController();
        $result = $loginController->index();


        $this->assertEquals('true', $result);
    }
}
