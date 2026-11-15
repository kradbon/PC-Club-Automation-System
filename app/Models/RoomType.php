<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $fillable = [
        'name', 'category', 'quantity', 'player_capacity', 'cpu', 'gpu', 'memory',
        'monitor', 'monitor_hz', 'peripherals', 'hourly_rate_cents', 'description',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean', 'quantity' => 'integer', 'player_capacity' => 'integer',
            'monitor_hz' => 'integer', 'hourly_rate_cents' => 'integer',
        ];
    }
}
