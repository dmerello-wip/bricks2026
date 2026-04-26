<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Color;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use App\Twill\Fields\BlockFields;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class AbstractBlock extends AppBlock
{
    public static function getBlockIdentifier(): string
    {
        return 'abstract';
    }

    public function render(): View
    {
        return view('components.twill.blocks.abstract');
    }

    public function getForm(): Form
    {
        return Form::make([
            ...BlockFields::inputWithSeoTag('eyelet', 'Eyelet'),
            ...BlockFields::inputWithSeoTag('title', 'Title'),
            ...BlockFields::inputWithSeoTag('subtitle', 'Subtitle'),
            Wysiwyg::make()->name('text')->translatable(),
            Medias::make()
                ->name('abstract_image')
                ->label('Image')
                ->max(1),
            Columns::make()
                ->left([
                    BlockFields::textColor(),
                ])
                ->right([
                    Select::make()
                        ->name('alignment')
                        ->label('Alignment')
                        ->options([
                            ['value' => 'left', 'label' => 'Left'],
                            ['value' => 'right', 'label' => 'Right'],
                        ])
                        ->default('left'),
                ]),
            Columns::make()
                ->left([
                    Color::make()
                        ->name('bg_color')
                        ->label('Background Color'),
                ])
                ->right([
                    Checkbox::make()
                        ->name('no_padding_bottom')
                        ->label('No padding bottom'),
                ]),
            InlineRepeater::make()
                ->name('ctas')
                ->label('CTAs')
                ->fields([
                    ...BlockFields::ctaFields(),
                ])
                ->max(3),
        ]);
    }
}
