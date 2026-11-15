<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pc extends Model
{
    protected $table = 'pcs';

    protected $fillable = ['pc_zone_id', 'name', 'asset_tag', 'specifications', 'status', 'is_bookable', 'maintenance_note'];

    protected function casts(): array
    {
        return ['specifications' => 'array', 'is_bookable' => 'boolean'];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(PcZone::class, 'pc_zone_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function maintenancePeriods(): HasMany
    {
        return $this->hasMany(PcMaintenancePeriod::class);
    }
}
