<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Color;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use App\Twill\Fields\BlockFields;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class MasonryGallery extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.masonry-gallery');
    }

    public function getForm(): Form
    {
        return Form::make([
            ...BlockFields::inputWithSeoTag('eyelet', 'Eyelet'),
            ...BlockFields::inputWithSeoTag('title', 'Title'),
            ...BlockFields::inputWithSeoTag('subtitle', 'Subtitle'),

            Columns::make()
                ->left([
                    BlockFields::textColor(),
                ])
                ->right([
                    Color::make()
                        ->name('bg_color')
                        ->label('Background Color'),
                ]),

            Checkbox::make()
                ->name('no_padding_bottom')
                ->label('No padding bottom'),

            InlineRepeater::make()
                ->name('masonry_items')
                ->label('Items')
                ->fields([
                    Medias::make()
                        ->name('masonry_image')
                        ->label('Image')
                        ->max(1),

                    InlineRepeater::make()
                        ->name('ctas')
                        ->label('CTA')
                        ->fields([
                            ...BlockFields::ctaFields(),
                        ])
                        ->max(1),
                ]),
        ]);
    }
}
