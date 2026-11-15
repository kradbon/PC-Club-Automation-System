<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_creates_wallet_and_keeps_chosen_language(): void
    {
        $this->post('/ru/join', [
            'name' => 'GB Player', 'email' => 'PLAYER@EXAMPLE.COM',
            'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123',
        ])->assertRedirect('/ru/account')->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'player@example.com', 'role' => 'customer', 'preferred_locale' => 'ru']);
        $this->assertDatabaseHas('wallets', ['cash_balance' => '0.00', 'bonus_balance' => '0.00']);
    }
}
