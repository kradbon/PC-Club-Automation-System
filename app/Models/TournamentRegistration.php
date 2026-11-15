<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentRegistration extends Model
{
    protected $fillable = ['tournament_id', 'user_id', 'team_id', 'registration_code', 'status', 'registered_at'];

    protected function casts(): array
    {
        return ['registered_at' => 'datetime'];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
