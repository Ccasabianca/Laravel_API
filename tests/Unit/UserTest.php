<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_uses_professional_email_true(): void
    {
        $user = new User();
        $user ->email = "john@entreprise.com";  
        $this->assertTrue($user->usesProfessionalEmail());
    }

     public function test_uses_professional_email_false(): void
    {
        $user = new User();
        $user ->email = "john@gmail.com";  
        $this->assertFalse($user->usesProfessionalEmail());
    }
}
