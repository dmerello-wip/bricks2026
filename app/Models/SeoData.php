<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoData extends Model
{
    use SoftDeletes;

    protected $table = 'seo_data';

    protected $fillable = [
        'no_index',
        'translations',
    ];

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'no_index' => 'boolean',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the translated SEO fields for the given locale.
     *
     * @return array{seo_title: string|null, seo_description: string|null, seo_keywords: string|null, og_title: string|null, og_description: string|null}
     */
    public function getForLocale(string $locale): array
    {
        $translations = $this->translations ?? [];

        return $translations[$locale]
            ?? $translations[config('translatable.fallback_locale')]
            ?? [];
    }
}
