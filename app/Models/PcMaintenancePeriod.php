<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcMaintenancePeriod extends Model
{
    protected $fillable = ['pc_id', 'starts_at', 'ends_at', 'reason', 'created_by'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function pc(): BelongsTo
    {
        return $this->belongsTo(Pc::class);
    }
}
