<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tariff extends Model
{
    protected $fillable = ['name', 'description', 'minutes', 'price_cents', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'minutes' => 'integer', 'price_cents' => 'integer'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ClubOrder::class);
    }
}
