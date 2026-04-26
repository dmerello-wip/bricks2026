<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Color;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use App\Twill\Fields\BlockFields;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class CardsList extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.cards-list');
    }

    public function getForm(): Form
    {
        return Form::make([
            Columns::make()
                ->left([
                    BlockFields::textAlignment(),
                ])
                ->right([
                    BlockFields::textColor(),
                ]),
            Columns::make()
                ->left([
                    Color::make()
                        ->name('bg_color')
                        ->label('Background Color'),
                ])
                ->right([
                    Checkbox::make()
                        ->name('no_margin')
                        ->label('No bottom margin'),
                ]),
            InlineRepeater::make()
                ->name('cards')
                ->label('Cards')
                ->fields([
                    ...BlockFields::inputWithSeoTag('eyelet', 'Eyelet'),
                    ...BlockFields::inputWithSeoTag('title', 'Title'),
                    Wysiwyg::make()->name('text')->translatable(),
                    Medias::make()->name('card_image')->label('Image')->max(1),
                    InlineRepeater::make()
                        ->name('ctas')
                        ->label('ctas')
                        ->fields([
                            ...BlockFields::ctaFields(),
                        ])
                        ->max(2),
                ])
                ->max(4),
        ]);
    }
}
