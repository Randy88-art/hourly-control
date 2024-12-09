<?php

declare(strict_types=1);

use Application\model\classes\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validate::class)]
final class ValidateTest extends TestCase
{    
    private Validate $validate;

    public function setUp(): void
    {                    
        $this->validate = new Validate();
    }

    // Validate e-mail
    public function testValidateEmail(): void
    {          
        $this->assertTrue($this->validate->validate_email('GQ9W8@example.com'));
        $this->assertFalse($this->validate->validate_email('test'));
    }

    // Validate form
    public function testValidateForm(): void
    {                        
        $this->assertTrue($this->validate->validate_form(['email' => 'GQ9W8@example.com']));
        $this->assertFalse($this->validate->validate_form(['email' => 'test']));
    }
    
    public function testGetMsg(): void
    {        
        $result = $this->validate->validate_form(['email' => 'test']);
        
        $this->assertSame("Insert a valid e-mail.", $this->validate->get_msg());
    }

    public function testCsrfToken(): void
    {        
        $csrfToken = $this->validate->csrf_token();
        $this->assertIsString($csrfToken);
    }

    // Validate input
    public function testValidateInput(): void
    {        
        $this->assertSame('test', $this->validate->test_input('test'));
        $this->assertSame(1, $this->validate->test_input(1));
        $this->assertNull($this->validate->test_input(null));
        $this->assertIsFloat($this->validate->test_input(1.1));
    }
}

