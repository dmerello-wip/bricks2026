<?php

namespace App\Twill\Fieldsets;

use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fieldset;

class SeoFieldset
{
    public static function make(): Fieldset
    {
        return Fieldset::make()
            ->title('SEO')
            ->id('seo')
            ->closed()
            ->fields([
                Columns::make()
                    ->left([
                        Input::make()
                            ->name('seo_title')
                            ->label('SEO Title')
                            ->translatable()
                            ->maxLength(60)
                            ->note('Shown in search results.'),

                    ])
                    ->right([
                        Input::make()
                            ->name('seo_description')
                            ->label('SEO Description')
                            ->translatable()
                            ->type('textarea')
                            ->maxLength(160)
                            ->note('Shown in search results.'),

                    ]),
                Columns::make()
                    ->left([
                        Input::make()
                            ->name('og_title')
                            ->label('OG Title')
                            ->translatable()
                            ->maxLength(60)
                            ->note('Shown when shared on social media.'),
                    ])
                    ->right([
                        Input::make()
                            ->name('og_description')
                            ->label('OG Description')
                            ->translatable()
                            ->type('textarea')
                            ->maxLength(160)
                            ->note('Shown when shared on social media.'),

                    ]),
                Medias::make()
                    ->name('seo_og_image')
                    ->label('OG Image')
                    ->note('Minimum 1200×630px, ratio 1.91:1. Used when shared on social media.'),

                Columns::make()
                    ->left([
                        Input::make()
                            ->name('canonical')
                            ->label('Canonical URL')
                            ->translatable()
                            ->note('Leave blank to use the page URL automatically.'),

                    ])
                    ->right([
                        Checkbox::make()
                            ->name('no_index')
                            ->label('No Index')
                            ->note('Prevent search engines from indexing this page.'),
                    ])
            ]);
    }
}
