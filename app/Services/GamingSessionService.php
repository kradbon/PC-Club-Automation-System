<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\GamingSession;
use App\Models\Pc;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GamingSessionService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function checkIn(Booking $booking, User $actor, int $pcId): GamingSession
    {
        return DB::transaction(function () use ($booking, $actor, $pcId): GamingSession {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            if ($locked->status !== 'confirmed' || $locked->gamingSession()->exists()) {
                throw ValidationException::withMessages(['booking' => __('dashboard.session_not_checkin')]);
            }
            if (now()->lt($locked->starts_at->copy()->subMinutes((int) config('club.session.checkin_early_minutes', 30))) || now()->gte($locked->ends_at)) {
                throw ValidationException::withMessages(['booking' => __('dashboard.session_outside_window')]);
            }

            $pc = Pc::query()->where('pc_zone_id', $locked->pc_zone_id)->lockForUpdate()->findOrFail($pcId);
            if ($locked->pc_id && (int) $locked->pc_id !== $pc->id) {
                throw ValidationException::withMessages(['pc_id' => __('dashboard.session_wrong_pc')]);
            }
            if (! $pc->is_bookable || $pc->status !== 'available') {
                throw ValidationException::withMessages(['pc_id' => __('dashboard.session_pc_unavailable')]);
            }
            if ($pc->maintenancePeriods()->where('starts_at', '<', $locked->ends_at)->where('ends_at', '>', now())->exists()) {
                throw ValidationException::withMessages(['pc_id' => __('dashboard.session_pc_unavailable')]);
            }
            $conflict = Booking::query()->where('id', '<>', $locked->id)->where('pc_id', $pc->id)->where('status', 'confirmed')
                ->where('starts_at', '<', $locked->ends_at)->where('ends_at', '>', now())->exists();
            if ($conflict) {
                throw ValidationException::withMessages(['pc_id' => __('dashboard.session_pc_conflict')]);
            }

            $session = GamingSession::create([
                'booking_id' => $locked->id, 'pc_id' => $pc->id, 'user_id' => $locked->user_id,
                'status' => 'checked_in', 'checked_in_by' => $actor->id, 'checked_in_at' => now(),
            ]);
            $this->activity->record($actor, 'gaming_session.checked_in', $session, ['booking_id' => $locked->id, 'pc_id' => $pc->id]);

            return $session->load(['booking', 'pc', 'user']);
        });
    }

    public function start(GamingSession $session, User $actor): GamingSession
    {
        return DB::transaction(function () use ($session, $actor): GamingSession {
            $locked = GamingSession::query()->lockForUpdate()->findOrFail($session->id);
            $booking = Booking::query()->lockForUpdate()->findOrFail($locked->booking_id);
            $pc = Pc::query()->lockForUpdate()->findOrFail($locked->pc_id);

            if ($locked->status !== 'checked_in' || $booking->status !== 'confirmed') {
                throw ValidationException::withMessages(['session' => __('dashboard.session_not_startable')]);
            }
            if (now()->lt($booking->starts_at) || now()->gte($booking->ends_at)) {
                throw ValidationException::withMessages(['session' => __('dashboard.session_outside_window')]);
            }
            if (! $pc->is_bookable || $pc->status !== 'available' || $this->pcHasActiveSession($pc, $booking)) {
                throw ValidationException::withMessages(['session' => __('dashboard.session_pc_unavailable')]);
            }

            $locked->status = 'active';
            $locked->started_by = $actor->id;
            $locked->started_at = now();
            $locked->save();
            $this->activity->record($actor, 'gaming_session.started', $locked, ['booking_id' => $booking->id, 'pc_id' => $pc->id]);

            return $locked->load(['booking', 'pc', 'user']);
        });
    }

    public function end(GamingSession $session, User $actor): GamingSession
    {
        return DB::transaction(function () use ($session, $actor): GamingSession {
            $locked = GamingSession::query()->lockForUpdate()->findOrFail($session->id);
            if ($locked->status !== 'active') {
                throw ValidationException::withMessages(['session' => __('dashboard.session_not_endable')]);
            }

            $locked->status = 'completed';
            $locked->ended_by = $actor->id;
            $locked->ended_at = now();
            $locked->save();

            $booking = Booking::query()->lockForUpdate()->findOrFail($locked->booking_id);
            if ($booking->status === 'confirmed') {
                $booking->status = 'completed';
                $booking->completed_at = now();
                $booking->save();
            }
            $this->activity->record($actor, 'gaming_session.completed', $locked, ['booking_id' => $booking->id]);

            return $locked->load(['booking', 'pc', 'user']);
        });
    }

    public function noShow(Booking $booking, User $actor): GamingSession
    {
        return DB::transaction(function () use ($booking, $actor): GamingSession {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $grace = (int) config('club.session.no_show_grace_minutes', 15);
            if ($locked->status !== 'confirmed' || $locked->gamingSession()->exists() || now()->lt($locked->starts_at->copy()->addMinutes($grace))) {
                throw ValidationException::withMessages(['booking' => __('dashboard.session_not_no_show')]);
            }

            $session = GamingSession::create([
                'booking_id' => $locked->id, 'pc_id' => $locked->pc_id, 'user_id' => $locked->user_id,
                'status' => 'no_show', 'no_show_by' => $actor->id, 'no_show_at' => now(),
            ]);
            $locked->status = 'completed';
            $locked->completed_at = now();
            $locked->decision_reason = 'No-show recorded by club staff.';
            $locked->save();
            $this->activity->record($actor, 'gaming_session.no_show', $session, ['booking_id' => $locked->id]);

            return $session->load(['booking', 'pc', 'user']);
        });
    }

    private function pcHasActiveSession(Pc $pc, Booking $booking): bool
    {
        return GamingSession::query()->where('pc_id', $pc->id)->whereIn('status', ['checked_in', 'active'])
            ->whereHas('booking', fn ($query) => $query->where('id', '<>', $booking->id)
                ->where('starts_at', '<', $booking->ends_at)->where('ends_at', '>', $booking->starts_at))
            ->exists();
    }
}
