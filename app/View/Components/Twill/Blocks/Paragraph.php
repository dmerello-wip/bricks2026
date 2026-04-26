<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Color;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;
use App\Twill\Fields\BlockFields;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class Paragraph extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.paragraph');
    }

    public function getForm(): Form
    {
        return Form::make([
            ...BlockFields::inputWithSeoTag('eyelet', 'Eyelet'),
            ...BlockFields::inputWithSeoTag('title', 'Title'),
            Wysiwyg::make()->name('text')->translatable(),
            Columns::make()
                ->left([
                    Select::make()
                        ->name('columns')
                        ->label('Number of Columns')
                        ->options([
                            ['value' => 'cols-1', 'label' => '1'],
                            ['value' => 'cols-2', 'label' => '2'],
                        ])
                        ->default('cols-1'),
                ])
                ->right([
                    BlockFields::textAlignment(),
                ]),
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
                ->name('no_margin')
                ->label('No bottom margin'),
        ]);
    }
}
