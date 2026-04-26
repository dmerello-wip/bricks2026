<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Browser;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Color;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use App\Twill\Fields\BlockFields;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class Matrix extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.matrix');
    }

    public function getForm(): Form
    {
        return Form::make([
            ...BlockFields::inputWithSeoTag('eyelet', 'Eyelet'),
            ...BlockFields::inputWithSeoTag('title', 'Title'),

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
                ->name('matrix_items')
                ->label('Items')
                ->fields([
                    Medias::make()
                        ->name('image')
                        ->label('Image')
                        ->max(1),

                    Columns::make()
                        ->left([
                            Select::make()
                                ->name('link_type')
                                ->label('Link Type')
                                ->options([
                                    ['value' => 'none', 'label' => 'None'],
                                    ['value' => 'external', 'label' => 'External'],
                                    ['value' => 'internal', 'label' => 'Internal'],
                                ])
                                ->default('none'),
                        ])
                        ->right([
                            Input::make()
                                ->name('link_external')
                                ->label('External URL')
                                ->connectedTo('link_type', 'external'),

                            Browser::make()
                                ->name('pages')
                                ->modules([['label' => 'Pages', 'name' => 'pages']])
                                ->label('Page')
                                ->max(1)
                                ->connectedTo('link_type', 'internal'),
                        ]),
                ]),
        ]);
    }
}
