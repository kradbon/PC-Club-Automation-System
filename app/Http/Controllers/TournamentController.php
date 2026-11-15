<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\Team;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::query()->whereIn('status', ['registration', 'published', 'in_progress', 'completed'])
            ->orderByRaw("CASE WHEN status = 'registration' THEN 0 ELSE 1 END")
            ->orderBy('starts_at')->paginate(12);

        return view('pages.tournaments', compact('tournaments'));
    }

    public function show(string $locale, Tournament $tournament): View
    {
        abort_unless(in_array($tournament->status, ['registration', 'published', 'in_progress', 'completed'], true), 404);
        $tournament->load(['registrations.user', 'matches']);
        $teams = auth()->check()
            ? Team::query()->where('captain_id', auth()->id())->withCount('members')->orderBy('name')->get()
            : collect();

        return view('pages.tournament', compact('tournament', 'teams'));
    }

    public function register(Request $request, string $locale, Tournament $tournament, ActivityLogger $activity): RedirectResponse
    {
        $user = $request->user();
        $input = $request->validate(['team_id' => ['nullable', 'integer', 'exists:teams,id']]);
        $registration = DB::transaction(function () use ($tournament, $user, $activity, $input): TournamentRegistration {
            $locked = Tournament::query()->lockForUpdate()->findOrFail($tournament->id);
            if ($locked->status !== 'registration' || $locked->registration_opens_at?->isFuture() || $locked->registration_closes_at?->isPast()) {
                throw ValidationException::withMessages(['tournament' => __('ui.tournament_registration_closed')]);
            }
            if ($locked->registration_fee > 0) {
                throw ValidationException::withMessages(['tournament' => __('ui.tournament_fee_unavailable')]);
            }
            $team = null;
            if ($locked->team_size > 1) {
                if (empty($input['team_id'])) {
                    throw ValidationException::withMessages(['team_id' => __('ui.choose_team')]);
                }
                $team = Team::query()->withCount('members')->where('captain_id', $user->id)->findOrFail($input['team_id']);
                if ($team->members_count < $locked->team_size) {
                    throw ValidationException::withMessages(['team_id' => __('ui.team_incomplete', ['count' => $locked->team_size])]);
                }
            } elseif (! empty($input['team_id'])) {
                throw ValidationException::withMessages(['team_id' => __('ui.individual_tournament')]);
            }
            if ($locked->registrations()->whereIn('status', ['pending', 'approved'])->count() >= $locked->participant_limit) {
                throw ValidationException::withMessages(['tournament' => __('ui.tournament_full')]);
            }

            $identity = ['tournament_id' => $locked->id, $team ? 'team_id' : 'user_id' => $team?->id ?? $user->id];
            $registration = TournamentRegistration::firstOrCreate(
                $identity,
                ['user_id' => $user->id, 'team_id' => $team?->id, 'registration_code' => 'GB-'.strtoupper(bin2hex(random_bytes(5))), 'status' => 'approved', 'registered_at' => now()],
            );
            if (! $registration->wasRecentlyCreated) {
                throw ValidationException::withMessages(['tournament' => __('ui.tournament_already_registered')]);
            }
            $activity->record($user, 'tournament.registered', $registration, ['tournament_id' => $locked->id]);

            return $registration;
        });

        return back()->with('success', __('ui.tournament_registered', ['code' => $registration->registration_code]));
    }

    public function cancelRegistration(Request $request, string $locale, Tournament $tournament, ActivityLogger $activity): RedirectResponse
    {
        $registration = $tournament->registrations()->where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($registration->status === 'approved' && $tournament->starts_at->isFuture(), 403);
        $registration->update(['status' => 'cancelled']);
        $activity->record($request->user(), 'tournament.registration_cancelled', $registration);

        return back()->with('success', __('ui.tournament_cancelled'));
    }
}
