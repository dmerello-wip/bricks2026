<?php

namespace App\Models\Concerns;

use App\Models\SeoData;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoData
{
    /**
     * Declared as a PHP class property so that assignment in initializeHasSeoData()
     * does NOT go through Eloquent's __set() and end up in $this->attributes.
     */
    public array $mediasParams = [];

    /**
     * Automatically injects mediasParams for seo_og_image so the developer
     * does not need to declare them manually on each module model.
     */
    public function initializeHasSeoData(): void
    {
        $this->mediasParams = array_merge($this->mediasParams, [
            'seo_og_image' => [
                'default' => [
                    ['name' => 'default', 'ratio' => 1200 / 630, 'minWidth' => 1200, 'minHeight' => 630],
                ],
            ],
        ]);
    }

    public function seoData(): MorphOne
    {
        return $this->morphOne(SeoData::class, 'seoable');
    }
}
