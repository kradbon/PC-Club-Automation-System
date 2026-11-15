<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PcZone extends Model
{
    protected $fillable = [
        'slug', 'category', 'booking_mode', 'name_translations', 'description_translations', 'hourly_rate',
        'currency', 'opens_at', 'closes_at', 'minimum_booking_minutes', 'maximum_booking_minutes',
        'maximum_days_ahead', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name_translations' => 'array',
            'description_translations' => 'array',
            'hourly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function pcs(): HasMany
    {
        return $this->hasMany(Pc::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function localized(string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $translations = match ($field) {
            'name' => $this->name_translations,
            'description' => $this->description_translations,
            default => [],
        } ?? [];

        return $translations[$locale] ?? $translations[config('app.fallback_locale', 'en')] ?? '';
    }
}
