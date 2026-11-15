<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function receive(Request $request, string $provider, WalletService $wallets): JsonResponse
    {
        $secret = (string) config('club.payment.webhook_secret');
        abort_if($secret === '' || $provider === 'simulation', 404);

        $signature = (string) $request->header('X-Payment-Signature');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        abort_unless(hash_equals($expected, $signature), 401, 'Invalid payment signature.');

        $data = $request->validate([
            'provider_reference' => ['required', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:succeeded,failed'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        DB::transaction(function () use ($provider, $data, $wallets): void {
            $payment = PaymentTransaction::query()
                ->where('idempotency_key', $data['idempotency_key'])
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($payment->provider === $provider && $payment->provider_reference === $data['provider_reference'], 409);
            abort_unless(Money::cents((string) $payment->amount) === Money::cents((string) $data['amount']), 409, 'Payment amount mismatch.');
            abort_unless($payment->currency === strtoupper($data['currency']), 409, 'Payment currency mismatch.');

            if ($payment->status !== 'created') {
                return;
            }

            if ($data['status'] === 'failed') {
                $payment->update(['status' => 'failed']);
                return;
            }

            $payment->update(['status' => 'succeeded', 'verified_at' => now()]);
            $user = User::findOrFail($payment->user_id);
            $wallets->ensureWallet($user);
            $wallets->credit(
                $user, 'cash', (string) $payment->amount,
                'cash_top_up', 'payment:'.$payment->id.':cash', 'Verified payment provider top-up.',
                null, PaymentTransaction::class, $payment->id,
            );
        });

        return response()->json(['received' => true]);
    }
}
