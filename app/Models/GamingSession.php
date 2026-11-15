<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamingSession extends Model
{
    protected $fillable = [
        'booking_id', 'pc_id', 'user_id', 'status', 'checked_in_by', 'checked_in_at',
        'started_by', 'started_at', 'ended_by', 'ended_at', 'no_show_by', 'no_show_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime', 'started_at' => 'datetime',
            'ended_at' => 'datetime', 'no_show_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function pc(): BelongsTo
    {
        return $this->belongsTo(Pc::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by');
    }
}
