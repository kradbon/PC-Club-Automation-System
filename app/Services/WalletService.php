<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function ensureWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['currency' => config('club.currency', 'TJS')]
        );
    }

    public function credit(
        User $user,
        string $bucket,
        string|int $amount,
        string $type,
        string $idempotencyKey,
        string $reason,
        ?User $actor = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): WalletTransaction {
        $amountCents = Money::cents($amount);
        abort_if($amountCents < 1, 422, 'The credit must be greater than zero.');
        abort_unless(in_array($bucket, ['cash', 'bonus'], true), 422);

        try {
            return DB::transaction(function () use ($user, $bucket, $amountCents, $type, $idempotencyKey, $reason, $actor, $referenceType, $referenceId) {
                if ($existing = WalletTransaction::where('idempotency_key', $idempotencyKey)->first()) {
                    return $this->assertSameCredit($existing, $user, $bucket, $amountCents, $type);
                }

                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();
                $column = $bucket.'_balance';
                $balance = Money::cents((string) $wallet->{$column}) + $amountCents;
                $wallet->{$column} = Money::decimal($balance);
                $wallet->save();

                return $this->record($wallet, $user, $bucket, $type, $amountCents, $balance, $idempotencyKey, $reason, $actor, $referenceType, $referenceId);
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $exception) {
            return $this->assertSameCredit(WalletTransaction::where('idempotency_key', $idempotencyKey)->firstOrFail(), $user, $bucket, $amountCents, $type);
        }
    }

    public function debitForBooking(Booking $booking, User $actor): array
    {
        return DB::transaction(function () use ($booking, $actor): array {
            $wallet = Wallet::where('user_id', $booking->user_id)->lockForUpdate()->firstOrFail();
            $totalCents = Money::cents((string) $booking->total_price_snapshot);
            $bonusAvailable = Money::cents((string) $wallet->bonus_balance);
            $cashAvailable = Money::cents((string) $wallet->cash_balance);
            $bonusCents = config('club.booking.bonus_first') ? min($bonusAvailable, $totalCents) : 0;
            $cashCents = $totalCents - $bonusCents;

            if ($cashAvailable < $cashCents) {
                throw ValidationException::withMessages([
                    'wallet' => __('ui.insufficient_wallet'),
                ]);
            }

            if ($bonusCents > 0) {
                $newBonus = $bonusAvailable - $bonusCents;
                $wallet->bonus_balance = Money::decimal($newBonus);
                $wallet->save();
                $this->record($wallet, $booking->user, 'bonus', 'booking_debit', $bonusCents, $newBonus, 'booking:'.$booking->id.':debit:bonus', 'Reservation payment', $actor, Booking::class, $booking->id);
            }

            if ($cashCents > 0) {
                $newCash = $cashAvailable - $cashCents;
                $wallet->cash_balance = Money::decimal($newCash);
                $wallet->save();
                $this->record($wallet, $booking->user, 'cash', 'booking_debit', $cashCents, $newCash, 'booking:'.$booking->id.':debit:cash', 'Reservation payment', $actor, Booking::class, $booking->id);
            }

            return ['cash' => Money::decimal($cashCents), 'bonus' => Money::decimal($bonusCents)];
        });
    }

    public function refundBooking(Booking $booking, User $actor): void
    {
        foreach (['cash', 'bonus'] as $bucket) {
            $cents = Money::cents((string) $booking->{$bucket.'_debited'});

            if ($cents > 0) {
                $this->credit(
                    $booking->user,
                    $bucket,
                    Money::decimal($cents),
                    'booking_refund',
                    'booking:'.$booking->id.':refund:'.$bucket,
                    'Reservation cancellation refund',
                    $actor,
                    Booking::class,
                    $booking->id,
                );
            }
        }
    }

    public function adjust(User $user, User $actor, string $bucket, string $direction, string|int $amount, string $reason, string $key): WalletTransaction
    {
        if ($direction === 'credit') {
            return $this->credit($user, $bucket, $amount, 'admin_adjustment_credit', $key, $reason, $actor);
        }

        $amountCents = Money::cents($amount);
        abort_if($amountCents < 1, 422);

        return DB::transaction(function () use ($user, $actor, $bucket, $amountCents, $reason, $key) {
            if ($existing = WalletTransaction::where('idempotency_key', $key)->first()) {
                abort_unless(
                    $existing->user_id === $user->id
                    && $existing->bucket === $bucket
                    && $existing->transaction_type === 'admin_adjustment_debit'
                    && Money::cents((string) $existing->amount) === $amountCents,
                    409,
                    'The idempotency key has already been used for a different adjustment.',
                );

                return $existing;
            }

            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $column = $bucket.'_balance';
            $balance = Money::cents((string) $wallet->{$column}) - $amountCents;
            if ($balance < 0) {
                throw ValidationException::withMessages(['amount' => __('ui.insufficient_wallet')]);
            }

            $wallet->{$column} = Money::decimal($balance);
            $wallet->save();

            return $this->record($wallet, $user, $bucket, 'admin_adjustment_debit', $amountCents, $balance, $key, $reason, $actor);
        });
    }

    private function record(
        Wallet $wallet,
        User $user,
        string $bucket,
        string $type,
        int $amountCents,
        int $balanceCents,
        string $key,
        string $reason,
        ?User $actor,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): WalletTransaction {
        return WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'actor_user_id' => $actor?->id,
            'transaction_type' => $type,
            'bucket' => $bucket,
            'amount' => Money::decimal($amountCents),
            'balance_after' => Money::decimal($balanceCents),
            'currency' => $wallet->currency,
            'idempotency_key' => $key,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }

    private function assertSameCredit(WalletTransaction $transaction, User $user, string $bucket, int $amountCents, string $type): WalletTransaction
    {
        abort_unless(
            $transaction->user_id === $user->id
            && $transaction->bucket === $bucket
            && $transaction->transaction_type === $type
            && Money::cents((string) $transaction->amount) === $amountCents,
            409,
            'The idempotency key has already been used for a different wallet credit.',
        );

        return $transaction;
    }
}
