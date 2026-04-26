<?php

namespace App\Repositories\Concerns;

use A17\Twill\Models\Contracts\TwillModelContract;

trait HandleSeoData
{
    /**
     * Called automatically by Twill's traitsMethods mechanism after every save.
     * Extracts SEO fields from the submitted form payload and persists them
     * to the polymorphic seo_data table. The parent module model does not need
     * translatedAttributes or mediasParams for SEO fields (mediasParams is
     * injected automatically by the HasSeoData model trait).
     */
    public function afterSaveHandleSeoData(TwillModelContract $object, array $fields): void
    {
        $translations = [];

        foreach (config('translatable.locales') as $locale) {
            $translations[$locale] = [
                'seo_title'       => $fields['seo_title'][$locale] ?? null,
                'seo_description' => $fields['seo_description'][$locale] ?? null,
                'canonical'       => $fields['canonical'][$locale] ?? null,
                'og_title'        => $fields['og_title'][$locale] ?? null,
                'og_description'  => $fields['og_description'][$locale] ?? null,
            ];
        }

        $object->seoData()->updateOrCreate(
            [],
            [
                'no_index'     => (bool) ($fields['no_index'] ?? false),
                'translations' => $translations,
            ]
        );
    }

    /**
     * Clean up seo_data when the module item is deleted.
     */
    public function afterDeleteHandleSeoData(TwillModelContract $object): void
    {
        $object->seoData()->delete();
    }
}
