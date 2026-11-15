<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodOrder extends Model
{
    protected $fillable = [
        'user_id', 'booking_id', 'accepted_by', 'request_key', 'order_number', 'fulfillment_method',
        'delivery_address', 'delivery_phone', 'location_note', 'subtotal_cents',
        'delivery_fee_cents', 'total_cents', 'currency', 'status', 'customer_note',
        'accepted_at', 'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'integer', 'delivery_fee_cents' => 'integer', 'total_cents' => 'integer',
            'accepted_at' => 'datetime', 'completed_at' => 'datetime', 'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FoodOrderItem::class);
    }

    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            'pending' => ['accepted', 'cancelled'],
            'accepted' => ['preparing', 'cancelled'],
            'preparing' => $this->fulfillment_method === 'delivery'
                ? ['out_for_delivery', 'cancelled']
                : ['ready', 'cancelled'],
            'ready', 'out_for_delivery' => ['completed', 'cancelled'],
            default => [],
        };
    }
}
