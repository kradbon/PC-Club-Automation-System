<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    protected $fillable = [
        'slug', 'game', 'name_translations', 'description_translations', 'prize_translations',
        'format', 'status', 'participant_limit', 'team_size', 'registration_opens_at',
        'registration_closes_at', 'starts_at', 'ends_at', 'registration_fee', 'currency', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'name_translations' => 'array', 'description_translations' => 'array', 'prize_translations' => 'array',
            'registration_opens_at' => 'datetime', 'registration_closes_at' => 'datetime',
            'starts_at' => 'datetime', 'ends_at' => 'datetime', 'registration_fee' => 'decimal:2',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function localized(string $field, ?string $locale = null): string
    {
        $translations = $this->{$field.'_translations'} ?? [];
        $locale ??= app()->getLocale();

        return $translations[$locale] ?? $translations[config('app.fallback_locale', 'en')] ?? '';
    }
}
