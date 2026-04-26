<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use App\Twill\Fields\BlockFields;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class EditorialCard extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.editorial-card');
    }

    public function getForm(): Form
    {
        return Form::make([
            ...BlockFields::inputWithSeoTag('eyelet', 'Eyelet'),
            ...BlockFields::inputWithSeoTag('title', 'Title'),
            Wysiwyg::make()->name('text')->translatable(),
            Medias::make()
                ->name('card_image')
                ->label('Image')
                ->max(1),
            InlineRepeater::make()
                ->name('ctas')
                ->label('CTAs')
                ->fields([
                    ...BlockFields::ctaFields(),
                ])
                ->max(2),
        ]);
    }
}
