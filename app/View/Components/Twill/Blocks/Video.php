<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Color;
use A17\Twill\Services\Forms\Fields\Files;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Form;
use App\Twill\Fields\BlockFields;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class Video extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.video');
    }

    public function getForm(): Form
    {
        return Form::make([

            Select::make()
                ->name('video_type')
                ->label('Video Source')
                ->options([
                    ['value' => 'file', 'label' => 'File'],
                    ['value' => 'youtube', 'label' => 'YouTube'],
                    ['value' => 'vimeo', 'label' => 'Vimeo'],
                ])
                ->default('file'),

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

            Color::make()
                ->name('bg_color')
                ->label('Background Color'),
                
            Checkbox::make()
                ->name('no_padding_bottom')
                ->label('No padding bottom'),
        ]);
    }
}
