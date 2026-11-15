<?php

namespace App\Contracts;

use App\Models\PaymentTransaction;
use App\Models\User;

interface PaymentGateway
{
    public function createTopUp(User $user, string $amount, string $currency, string $idempotencyKey): PaymentTransaction;
}
