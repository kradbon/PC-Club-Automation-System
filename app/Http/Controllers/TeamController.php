<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        $teams = Team::query()->whereHas('members', fn ($members) => $members->where('user_id', $request->user()->id))
            ->with(['captain', 'members.user'])->orderBy('name')->get();

        return view('account.teams', compact('teams'));
    }

    public function store(Request $request, string $locale, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $team = DB::transaction(function () use ($request, $data): Team {
            $team = Team::create([
                'name' => $data['name'], 'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(6)),
                'captain_id' => $request->user()->id, 'created_by' => $request->user()->id,
            ]);
            $team->members()->create(['user_id' => $request->user()->id, 'role' => 'captain']);

            return $team;
        });
        $activity->record($request->user(), 'team.created', $team);

        return back()->with('success', 'Team created. Invite your squad to join.');
    }

    public function addMember(Request $request, string $locale, Team $team, ActivityLogger $activity): RedirectResponse
    {
        abort_unless($team->captain_id === $request->user()->id, 403);
        $data = $request->validate(['email' => ['required', 'email', 'exists:users,email']]);
        $memberId = \App\Models\User::query()->where('email', Str::lower($data['email']))->value('id');
        abort_if($team->members()->where('user_id', $memberId)->exists(), 409, 'This player is already on the team.');
        $team->members()->create(['user_id' => $memberId, 'role' => 'member']);
        $activity->record($request->user(), 'team.member_added', $team, ['member_id' => $memberId]);

        return back()->with('success', 'Player added to team.');
    }

    public function removeMember(Request $request, string $locale, Team $team, TeamMember $member, ActivityLogger $activity): RedirectResponse
    {
        abort_unless($team->captain_id === $request->user()->id && $member->team_id === $team->id && $member->role !== 'captain', 403);
        $memberId = $member->user_id;
        $member->delete();
        $activity->record($request->user(), 'team.member_removed', $team, ['member_id' => $memberId]);

        return back()->with('success', 'Player removed from team.');
    }
}
