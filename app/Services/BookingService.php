<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Pc;
use App\Models\PcZone;
use App\Models\User;
use App\Notifications\BookingStatusNotification;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly ActivityLogger $activity,
    ) {}

    public function request(User $user, array $input, ?User $requestedBy = null): Booking
    {
        $zone = PcZone::query()->where('is_active', true)->findOrFail($input['pc_zone_id']);
        $startsAt = Carbon::parse($input['starts_at']);
        $duration = (int) $input['duration_minutes'];
        $endsAt = $startsAt->copy()->addMinutes($duration);

        $this->assertWindow($zone, $startsAt, $endsAt, $duration);

        if ($zone->booking_mode === 'station') {
            if (empty($input['pc_id'])) {
                throw ValidationException::withMessages(['pc_id' => __('ui.select_pc')]);
            }

            $pc = Pc::query()->where('pc_zone_id', $zone->id)->findOrFail($input['pc_id']);
            if (! $pc->is_bookable || $pc->status !== 'available') {
                throw ValidationException::withMessages(['pc_id' => __('ui.pc_unavailable')]);
            }
        } elseif (! empty($input['pc_id'])) {
            throw ValidationException::withMessages(['pc_id' => __('ui.private_zone_pc')]);
        }

        if ($user->bookings()->whereIn('status', ['pending', 'confirmed'])->count() >= (int) config('club.booking.maximum_active_per_user', 4)) {
            throw ValidationException::withMessages(['booking' => __('ui.booking_limit')]);
        }

        $booking = Booking::create([
            'user_id' => $user->id,
            'pc_zone_id' => $zone->id,
            'pc_id' => $input['pc_id'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'pending',
            'customer_note' => $input['customer_note'] ?? null,
            'currency' => $zone->currency,
        ]);

        $this->activity->record($requestedBy ?? $user, 'booking.requested', $booking, [
            'zone_id' => $zone->id,
            'duration_minutes' => $duration,
            'for_customer_id' => $requestedBy && $requestedBy->is($user) === false ? $user->id : null,
        ]);

        return $booking;
    }

    public function confirm(Booking $booking, User $staff): Booking
    {
        return DB::transaction(function () use ($booking, $staff): Booking {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['booking' => __('ui.booking_already_processed')]);
            }

            $zone = PcZone::query()->lockForUpdate()->findOrFail($locked->pc_zone_id);
            if (! $zone->is_active) {
                throw ValidationException::withMessages(['booking' => __('ui.zone_unavailable')]);
            }

            $duration = (int) $locked->starts_at->diffInMinutes($locked->ends_at);
            $this->assertWindow($zone, $locked->starts_at, $locked->ends_at, $duration);
            $pc = null;

            if ($zone->booking_mode === 'station') {
                $pc = Pc::query()->where('pc_zone_id', $zone->id)->lockForUpdate()->findOrFail($locked->pc_id);
                if (! $pc->is_bookable || $pc->status !== 'available' || $this->hasMaintenance($pc, $locked->starts_at, $locked->ends_at)) {
                    throw ValidationException::withMessages(['booking' => __('ui.pc_unavailable')]);
                }

                if ($this->hasConfirmedConflict($locked, $locked->starts_at, $locked->ends_at, $pc->id, false)) {
                    throw ValidationException::withMessages(['booking' => __('ui.booking_conflict')]);
                }
            } else {
                if ($this->hasConfirmedConflict($locked, $locked->starts_at, $locked->ends_at, null, true)) {
                    throw ValidationException::withMessages(['booking' => __('ui.booking_conflict')]);
                }

                if ($zone->pcs()->whereHas('maintenancePeriods', fn ($query) => $query
                    ->where('starts_at', '<', $locked->ends_at)
                    ->where('ends_at', '>', $locked->starts_at))->exists()) {
                    throw ValidationException::withMessages(['booking' => __('ui.pc_unavailable')]);
                }
            }

            $rateCents = Money::cents((string) $zone->hourly_rate);
            $totalCents = intdiv(($rateCents * $duration) + 30, 60);
            $locked->hourly_rate_snapshot = Money::decimal($rateCents);
            $locked->total_price_snapshot = Money::decimal($totalCents);
            $locked->currency = $zone->currency;

            $debits = $this->wallets->debitForBooking($locked, $staff);
            $locked->cash_debited = $debits['cash'];
            $locked->bonus_debited = $debits['bonus'];
            $locked->pc_id = $pc?->id;
            $locked->status = 'confirmed';
            $locked->confirmed_by = $staff->id;
            $locked->confirmed_at = now();
            $locked->save();

            $this->activity->record($staff, 'booking.confirmed', $locked, ['total' => $locked->total_price_snapshot, 'currency' => $locked->currency]);
            DB::afterCommit(fn () => $locked->user->notify(new BookingStatusNotification($locked, 'confirmed')));

            return $locked->fresh(['zone', 'pc', 'user']);
        });
    }

    public function decline(Booking $booking, User $staff, string $reason): Booking
    {
        return DB::transaction(function () use ($booking, $staff, $reason): Booking {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['booking' => __('ui.booking_already_processed')]);
            }

            $locked->status = 'declined';
            $locked->decision_reason = $reason;
            $locked->confirmed_by = $staff->id;
            $locked->save();

            $this->activity->record($staff, 'booking.declined', $locked, ['reason' => $reason]);
            DB::afterCommit(fn () => $locked->user->notify(new BookingStatusNotification($locked, 'declined')));

            return $locked->fresh();
        });
    }

    public function cancel(Booking $booking, User $actor): Booking
    {
        return DB::transaction(function () use ($booking, $actor): Booking {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $staffOverride = $actor->isAdmin() || $actor->isStaff();

            if ($locked->user_id !== $actor->id && ! $staffOverride) {
                abort(403);
            }

            if (! in_array($locked->status, ['pending', 'confirmed'], true)) {
                throw ValidationException::withMessages(['booking' => __('ui.booking_not_cancellable')]);
            }
            if ($locked->gamingSession()->exists()) {
                throw ValidationException::withMessages(['booking' => __('ui.booking_not_cancellable')]);
            }

            if ($locked->status === 'confirmed') {
                $cutoff = (int) config('club.booking.cancellation_cutoff_minutes', 120);
                if (! $staffOverride && $locked->starts_at->lessThan(now()->addMinutes($cutoff))) {
                    throw ValidationException::withMessages(['booking' => __('ui.cancellation_window_passed')]);
                }

                $this->wallets->refundBooking($locked, $actor);
            }

            $locked->status = 'cancelled';
            $locked->cancelled_at = now();
            $locked->decision_reason = $staffOverride ? 'Cancelled by club staff.' : 'Cancelled by customer.';
            $locked->save();

            $this->activity->record($actor, 'booking.cancelled', $locked, ['staff_override' => $staffOverride]);
            DB::afterCommit(fn () => $locked->user->notify(new BookingStatusNotification($locked, 'cancelled')));

            return $locked->fresh();
        });
    }

    public function complete(Booking $booking, User $staff): Booking
    {
        $locked = DB::transaction(function () use ($booking, $staff): Booking {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            if ($locked->status !== 'confirmed' || $locked->ends_at->isFuture()) {
                throw ValidationException::withMessages(['booking' => __('ui.booking_not_complete')]);
            }
            if ($locked->gamingSession()->whereIn('status', ['checked_in', 'active'])->exists()) {
                throw ValidationException::withMessages(['booking' => __('ui.booking_not_complete')]);
            }

            $locked->status = 'completed';
            $locked->completed_at = now();
            $locked->save();
            $this->activity->record($staff, 'booking.completed', $locked);

            return $locked;
        });

        return $locked;
    }

    private function assertWindow(PcZone $zone, Carbon $startsAt, Carbon $endsAt, int $duration): void
    {
        $minimum = max((int) config('club.booking.minimum_minutes', 60), (int) $zone->minimum_booking_minutes);
        $maximum = min((int) config('club.booking.maximum_minutes', 480), (int) $zone->maximum_booking_minutes);
        if ($duration < $minimum || $duration > $maximum) {
            throw ValidationException::withMessages(['duration_minutes' => __('ui.booking_duration_invalid', ['min' => $minimum, 'max' => $maximum])]);
        }

        if ($startsAt->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages(['starts_at' => __('ui.booking_start_future')]);
        }

        $maxDays = min((int) config('club.booking.maximum_days_ahead', 30), (int) $zone->maximum_days_ahead);
        if ($startsAt->greaterThan(now()->addDays($maxDays))) {
            throw ValidationException::withMessages(['starts_at' => __('ui.booking_too_far')]);
        }

        $open = $zone->opens_at ?: config('club.opening_hours.start', '10:00');
        $close = $zone->closes_at ?: config('club.opening_hours.end', '02:00');
        $this->assertWithinHours($startsAt, $endsAt, (string) $open, (string) $close);
    }

    private function assertWithinHours(Carbon $startsAt, Carbon $endsAt, string $open, string $close): void
    {
        $startClock = $startsAt->format('H:i:s');
        $openClock = strlen($open) === 5 ? $open.':00' : $open;
        $closeClock = strlen($close) === 5 ? $close.':00' : $close;
        $overnight = $closeClock <= $openClock;

        if (! $overnight) {
            $opening = $startsAt->copy()->setTimeFromTimeString($openClock);
            $closing = $startsAt->copy()->setTimeFromTimeString($closeClock);
            $valid = $startClock >= $openClock && $endsAt->lessThanOrEqualTo($closing);
        } elseif ($startClock >= $openClock) {
            $opening = $startsAt->copy()->setTimeFromTimeString($openClock);
            $closing = $startsAt->copy()->addDay()->setTimeFromTimeString($closeClock);
            $valid = $endsAt->lessThanOrEqualTo($closing);
        } elseif ($startClock < $closeClock) {
            $opening = $startsAt->copy()->subDay()->setTimeFromTimeString($openClock);
            $closing = $startsAt->copy()->setTimeFromTimeString($closeClock);
            $valid = $endsAt->lessThanOrEqualTo($closing);
        } else {
            $valid = false;
            $opening = $startsAt;
            $closing = $startsAt;
        }

        if (! $valid || $startsAt->lessThan($opening)) {
            throw ValidationException::withMessages(['starts_at' => __('ui.booking_outside_hours', ['open' => substr($openClock, 0, 5), 'close' => substr($closeClock, 0, 5)])]);
        }
    }

    private function hasMaintenance(Pc $pc, Carbon $start, Carbon $end): bool
    {
        return $pc->maintenancePeriods()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();
    }

    private function hasConfirmedConflict(Booking $booking, Carbon $start, Carbon $end, ?int $pcId, bool $wholeZone): bool
    {
        $query = Booking::query()
            ->where('id', '!=', $booking->id)
            ->where('status', 'confirmed')
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start);

        if ($wholeZone) {
            $query->where('pc_zone_id', $booking->pc_zone_id);
        } else {
            $query->where('pc_id', $pcId);
        }

        return $query->exists();
    }
}
