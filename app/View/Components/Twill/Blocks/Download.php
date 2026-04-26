<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Fields\Files;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class Download extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.download');
    }

    public function getForm(): Form
    {
        return Form::make([
            InlineRepeater::make()
                ->name('accordion_groups')
                ->label('Accordion Groups')
                ->fields([
                    Input::make()
                        ->name('title')
                        ->label('Group Title')
                        ->translatable(),

                    InlineRepeater::make()
                        ->name('accordion_items')
                        ->label('Items')
                        ->fields([
                            Input::make()
                                ->name('title')
                                ->label('Title')
                                ->translatable(),

                            Input::make()
                                ->name('description')
                                ->label('Description')
                                ->translatable(),

                            Files::make()
                                ->name('download_url')
                                ->label('Download File')
                                ->max(1),
                        ]),
                ]),
        ]);
    }
}
