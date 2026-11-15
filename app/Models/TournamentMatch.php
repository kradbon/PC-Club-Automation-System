<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentMatch extends Model
{
    protected $fillable = [
        'tournament_id', 'round_number', 'match_number', 'team_one_id', 'team_two_id',
        'winner_team_id', 'team_one_score', 'team_two_score', 'scheduled_at', 'status',
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime'];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function teamOne(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_one_id');
    }

    public function teamTwo(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_two_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }
}
