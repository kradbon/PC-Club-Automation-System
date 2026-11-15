<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodOrderItem extends Model
{
    protected $fillable = [
        'food_order_id', 'food_item_id', 'item_name', 'quantity', 'unit_price_cents', 'line_total_cents',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer', 'unit_price_cents' => 'integer', 'line_total_cents' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(FoodOrder::class, 'food_order_id');
    }

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class);
    }
}
