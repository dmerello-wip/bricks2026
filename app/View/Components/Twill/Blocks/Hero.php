<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use App\Twill\Fields\BlockFields;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class Hero extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.hero');
    }

    public function getForm(): Form
    {
        return Form::make([
            ...BlockFields::inputWithSeoTag('eyelet', 'Eyelet'),
            ...BlockFields::inputWithSeoTag('title', 'Title'),
            ...BlockFields::inputWithSeoTag('subtitle', 'Subtitle'),
            Wysiwyg::make()->name('text')->translatable(),
            Columns::make()
                ->left([
                    BlockFields::textColor(),
                ])
                ->right([
                    BlockFields::textAlignment(),
                ]),
            Columns::make()
                ->left([
                    Checkbox::make()
                        ->name('full_height')
                        ->label('Full height'),
                ])
                ->right([
                    Checkbox::make()
                        ->name('text_under_mobile')
                        ->label('Text under image on mobile'),
                ]),
            Medias::make()
                ->name('hero_image_desktop')
                ->label('Image Desktop')
                ->max(1),
            Medias::make()
                ->name('hero_image_mobile')
                ->label('Image Mobile')
                ->max(1),
            InlineRepeater::make()
                ->name('ctas')
                ->label('ctas')
                ->fields([
                    ...BlockFields::ctaFields(),
                ])
                ->max(3),

        ]);
    }
}
