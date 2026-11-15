<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodItem extends Model
{
    protected $fillable = [
        'slug', 'name', 'category', 'description', 'price_cents', 'currency',
        'image_url', 'is_available', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['price_cents' => 'integer', 'is_available' => 'boolean', 'sort_order' => 'integer'];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(FoodOrderItem::class);
    }
}
