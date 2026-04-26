<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\SingletonModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Form;

class SeoDefaultController extends SingletonModuleController
{
    protected $moduleName = 'seoDefaults';

    protected function setUpController(): void
    {
        $this->disablePermalink();
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->add(
            Input::make()
                ->name('default_title')
                ->label('Default SEO Title')
                ->translatable()
                ->maxLength(60)
                ->note('Used as fallback when a page has no SEO title. Max 60 characters.')
        );

        $form->add(
            Input::make()
                ->name('default_description')
                ->label('Default SEO Description')
                ->translatable()
                ->type('textarea')
                ->maxLength(160)
                ->note('Used as fallback when a page has no SEO description. Max 160 characters.')
        );

        $form->add(
            Input::make()
                ->name('default_og_title')
                ->label('Default Open Graph Title')
                ->translatable()
                ->maxLength(60)
                ->note('Used as fallback when a page has no OG title. Max 60 characters.')
        );

        $form->add(
            Input::make()
                ->name('default_og_description')
                ->label('Default Open Graph Description')
                ->translatable()
                ->type('textarea')
                ->maxLength(160)
                ->note('Used as fallback when a page has no OG description.')
        );

        $form->add(
            Medias::make()
                ->name('default_og_image')
                ->label('Default Open Graph Image')
                ->note('Minimum 1200×630px, ratio 1.91:1. Used as fallback on social sharing.')
        );

        return $form;
    }
}
