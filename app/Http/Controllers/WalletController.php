<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGateway;
use App\Http\Requests\TopUpWalletRequest;
use App\Models\PaymentTransaction;
use App\Services\ActivityLogger;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function show(Request $request, string $locale, WalletService $wallets): View
    {
        $wallet = $wallets->ensureWallet($request->user());
        $transactions = $wallet->transactions()->latest('created_at')->paginate(20);
        $payments = PaymentTransaction::where('user_id', $request->user()->id)->latest()->limit(10)->get();

        return view('account.wallet', compact('wallet', 'transactions', 'payments'));
    }

    public function topUp(TopUpWalletRequest $request, string $locale, PaymentGateway $gateway, WalletService $wallets, ActivityLogger $activity): RedirectResponse
    {
        abort_unless(config('club.payment.driver') === 'simulation' && app()->environment(['local', 'testing']), 503, __('ui.payments_not_configured'));

        $data = $request->validated();
        $cents = Money::cents((string) $data['amount']);
        abort_if($cents > Money::cents((string) config('club.payment.simulation_amount_limit', 500)), 422, __('ui.simulation_limit'));
        $user = $request->user();
        $idempotencyKey = 'simulation:'.$user->id.':'.$data['idempotency_key'];
        try {
            $payment = DB::transaction(function () use ($user, $gateway, $wallets, $activity, $cents, $idempotencyKey) {
                $existing = PaymentTransaction::query()->where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
                if ($existing) {
                    abort_unless(
                        $existing->user_id === $user->id
                        && Money::cents((string) $existing->amount) === $cents
                        && $existing->currency === config('club.currency', 'TJS')
                        && $existing->status === 'simulated',
                        409,
                        'This payment request key has already been used.',
                    );
                    $payment = $existing;
                } else {
                    $wallets->ensureWallet($user);
                    $payment = $gateway->createTopUp($user, Money::decimal($cents), config('club.currency', 'TJS'), $idempotencyKey);
                }

                $wallets->credit(
                    $user,
                    'cash',
                    Money::decimal($cents),
                    'simulated_top_up',
                    'payment:'.$payment->id.':cash',
                    'Development payment simulation; no funds collected.',
                    null,
                    PaymentTransaction::class,
                    $payment->id,
                );
                if ($payment->wasRecentlyCreated) {
                    $activity->record($user, 'wallet.top_up_simulated', $payment, ['amount' => $payment->amount, 'currency' => $payment->currency]);
                }

                return $payment;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $exception) {
            $payment = PaymentTransaction::where('idempotency_key', $idempotencyKey)->firstOrFail();
            abort_unless($payment->user_id === $user->id && Money::cents((string) $payment->amount) === $cents && $payment->status === 'simulated', 409);
            $wallets->credit($user, 'cash', Money::decimal($cents), 'simulated_top_up', 'payment:'.$payment->id.':cash', 'Development payment simulation; no funds collected.', null, PaymentTransaction::class, $payment->id);
        }

        return back()->with('success', __('ui.simulated_credit_notice', ['amount' => number_format($cents / 100, 2)], app()->getLocale()));
    }
}
