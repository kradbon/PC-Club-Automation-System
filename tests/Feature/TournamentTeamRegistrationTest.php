<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TournamentTeamRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_team_captain_can_register_team_once(): void
    {
        $captain = User::factory()->create(['role' => 'customer']);
        $team = Team::create(['name' => 'GB Five', 'slug' => 'gb-five', 'captain_id' => $captain->id]);
        foreach (range(0, 4) as $index) {
            $member = $index === 0 ? $captain : User::factory()->create(['role' => 'customer']);
            $team->members()->create(['user_id' => $member->id, 'role' => $index === 0 ? 'captain' : 'member']);
        }
        $tournament = Tournament::create([
            'slug' => 'team-test', 'game' => 'Counter-Strike 2', 'name_translations' => ['en' => 'Team Test'],
            'description_translations' => ['en' => 'Test tournament'], 'format' => 'single_elimination', 'status' => 'registration',
            'participant_limit' => 8, 'team_size' => 5, 'registration_opens_at' => now()->subHour(),
            'registration_closes_at' => now()->addDay(), 'starts_at' => now()->addDays(2), 'registration_fee' => '0.00',
            'currency' => 'TJS',
        ]);

        $response = $this->actingAs($captain)->post('/en/account/tournaments/'.$tournament->id.'/register', ['team_id' => $team->id]);
        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('tournament_registrations', [
            'tournament_id' => $tournament->id, 'team_id' => $team->id, 'user_id' => $captain->id,
        ]);
        $this->actingAs($captain)->post('/en/account/tournaments/'.$tournament->id.'/register', ['team_id' => $team->id])
            ->assertSessionHasErrors('tournament');
        $this->assertDatabaseCount('tournament_registrations', 1);
    }
}
