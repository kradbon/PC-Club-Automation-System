<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'user_id', 'provider', 'provider_reference', 'amount', 'currency', 'status',
        'idempotency_key', 'verified_at', 'metadata',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'metadata' => 'array', 'verified_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
