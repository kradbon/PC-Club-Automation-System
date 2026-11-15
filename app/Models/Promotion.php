<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'code', 'title_translations', 'description_translations', 'reward_type', 'reward_value',
        'minimum_purchase', 'usage_limit', 'uses_count', 'starts_at', 'ends_at', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'title_translations' => 'array', 'description_translations' => 'array',
            'reward_value' => 'decimal:2', 'minimum_purchase' => 'decimal:2',
            'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean',
        ];
    }

    public function localized(string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $translations = $this->{$field.'_translations'} ?? [];

        return $translations[$locale] ?? $translations[config('app.fallback_locale', 'en')] ?? '';
    }
}
