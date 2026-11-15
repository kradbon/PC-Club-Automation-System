<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubEvent extends Model
{
    protected $fillable = [
        'title', 'type', 'description', 'starts_at', 'ends_at',
        'image_url', 'capacity', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

}
