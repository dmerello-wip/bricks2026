<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Color;
use A17\Twill\Services\Forms\Fields\Files;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Radios;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use App\Twill\Fields\BlockFields;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class Gallery extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.gallery');
    }

    public function getForm(): Form
    {
        return Form::make([
            Columns::make()
                ->left([
                    Color::make()
                        ->name('bg_color')
                        ->label('Background Color'),
                ])
                ->right([
                    BlockFields::textColor(),
                ]),

            Checkbox::make()
                ->name('no_padding_bottom')
                ->label('No padding bottom'),

            InlineRepeater::make()
                ->name('gallery_items')
                ->label('Gallery Items')
                ->fields([
                    Radios::make()
                        ->name('item_type')
                        ->label('Item Type')
                        ->inline()
                        ->options([
                            ['value' => 'image', 'label' => 'Image'],
                            ['value' => 'video', 'label' => 'Video'],
                        ])
                        ->default('image'),

                    Medias::make()
                        ->name('gallery_image')
                        ->label('Image')
                        ->max(1)
                        ->connectedTo('item_type', 'image'),

                    Select::make()
                        ->name('video_type')
                        ->label('Video Source')
                        ->options([
                            ['value' => 'file', 'label' => 'File'],
                            ['value' => 'youtube', 'label' => 'YouTube'],
                            ['value' => 'vimeo', 'label' => 'Vimeo'],
                        ])
                        ->default('file')
                        ->connectedTo('item_type', 'video'),

                    Files::make()
                        ->name('video_file')
                        ->label('Video File')
                        ->max(1)
                        ->connectedTo('video_type', 'file'),

                    Input::make()
                        ->name('youtube_id')
                        ->label('YouTube ID')
                        ->connectedTo('video_type', 'youtube'),

                    Input::make()
                        ->name('vimeo_id')
                        ->label('Vimeo ID')
                        ->connectedTo('video_type', 'vimeo'),

                    Input::make()
                        ->name('caption')
                        ->label('Caption')
                        ->translatable(),
                ]),
        ]);
    }
}
