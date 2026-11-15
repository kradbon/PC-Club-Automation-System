<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'slug', 'title_translations', 'body_translations', 'meta_title_translations',
        'meta_description_translations', 'is_published', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'title_translations' => 'array', 'body_translations' => 'array',
            'meta_title_translations' => 'array', 'meta_description_translations' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function localized(string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $translations = $this->{$field.'_translations'} ?? [];

        return $translations[$locale] ?? $translations[config('app.fallback_locale', 'en')] ?? '';
    }
}
