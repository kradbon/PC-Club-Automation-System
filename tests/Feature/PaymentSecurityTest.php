<?php

namespace Tests\Feature;

use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulated_top_up_is_labeled_and_credits_only_local_wallet(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => 'customer']);
        Wallet::create(['user_id' => $user->id, 'cash_balance' => '0.00', 'bonus_balance' => '0.00', 'currency' => 'TJS']);

        $request = ['amount' => '25.00', 'idempotency_key' => '623d41a7-c537-4f5c-9acf-24b2ff2e8e93'];
        $this->actingAs($user)->post('/en/account/wallet/top-up', $request)
            ->assertRedirect()->assertSessionHas('success');
        $this->post('/en/account/wallet/top-up', $request)->assertRedirect();

        $this->assertSame('25.00', $user->wallet->fresh()->cash_balance);
        $this->assertSame('simulated', PaymentTransaction::sole()->status);
        $this->assertDatabaseCount('payment_transactions', 1);
        $this->assertSame('simulated_top_up', WalletTransaction::sole()->transaction_type);
    }

    public function test_valid_payment_webhook_credits_once_and_rejects_invalid_signature(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Wallet::create(['user_id' => $user->id, 'cash_balance' => '0.00', 'bonus_balance' => '0.00', 'currency' => 'TJS']);
        $payment = PaymentTransaction::create([
            'user_id' => $user->id, 'provider' => 'card-test', 'provider_reference' => 'PAY-100',
            'amount' => '37.50', 'currency' => 'TJS', 'status' => 'created', 'idempotency_key' => 'pay-event-100',
        ]);
        config(['club.payment.webhook_secret' => 'test-hook-secret']);
        $payload = json_encode([
            'provider_reference' => $payment->provider_reference, 'idempotency_key' => $payment->idempotency_key,
            'status' => 'succeeded', 'amount' => '37.50', 'currency' => 'TJS',
        ], JSON_THROW_ON_ERROR);
        $uri = '/webhooks/payments/card-test';

        $this->call('POST', $uri, [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYMENT_SIGNATURE' => 'wrong'], $payload)->assertUnauthorized();
        $signature = hash_hmac('sha256', $payload, 'test-hook-secret');
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYMENT_SIGNATURE' => $signature];
        $this->call('POST', $uri, [], [], [], $server, $payload)->assertOk();
        $this->call('POST', $uri, [], [], [], $server, $payload)->assertOk();

        $this->assertSame('37.50', $user->wallet->fresh()->cash_balance);
        $this->assertSame('succeeded', $payment->fresh()->status);
        $this->assertSame(1, WalletTransaction::where('idempotency_key', 'payment:'.$payment->id.':cash')->count());
    }
}
