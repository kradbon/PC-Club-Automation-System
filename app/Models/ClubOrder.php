<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubOrder extends Model
{
    protected $fillable = [
        'user_id', 'booking_id', 'tariff_id', 'processed_by', 'order_number', 'tariff_name', 'total_cents',
        'minutes', 'status', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime', 'total_cents' => 'integer', 'minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
