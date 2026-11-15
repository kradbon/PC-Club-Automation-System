<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Str;

class SimulatedPaymentGateway implements PaymentGateway
{
    public function createTopUp(User $user, string $amount, string $currency, string $idempotencyKey): PaymentTransaction
    {
        abort_unless(app()->environment(['local', 'testing']), 503, 'Simulated payments are disabled outside local development and tests.');

        return PaymentTransaction::create([
            'user_id' => $user->id,
            'provider' => 'simulation',
            'provider_reference' => 'SIM-'.Str::upper(Str::random(16)),
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'simulated',
            'idempotency_key' => $idempotencyKey,
            'metadata' => ['simulation_only' => true],
        ]);
    }
}
