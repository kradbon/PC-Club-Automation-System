<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'pc_zone_id', 'pc_id', 'starts_at', 'ends_at', 'status', 'customer_note',
        'decision_reason', 'confirmed_by', 'hourly_rate_snapshot', 'total_price_snapshot',
        'cash_debited', 'bonus_debited', 'currency', 'confirmed_at', 'cancelled_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime', 'ends_at' => 'datetime', 'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime', 'completed_at' => 'datetime',
            'hourly_rate_snapshot' => 'decimal:2', 'total_price_snapshot' => 'decimal:2',
            'cash_debited' => 'decimal:2', 'bonus_debited' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(PcZone::class, 'pc_zone_id');
    }

    public function pc(): BelongsTo
    {
        return $this->belongsTo(Pc::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function gamingSession(): HasOne
    {
        return $this->hasOne(GamingSession::class);
    }
}
