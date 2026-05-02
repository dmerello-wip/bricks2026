<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Fields\Browser;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use App\View\Components\Twill\AppBlock;
use Illuminate\Contracts\View\View;

class SubscriptionForm extends AppBlock
{
    public function render(): View
    {
        return view('components.twill.blocks.subscription-form');
    }

    public function getForm(): Form
    {
        return Form::make([
            Input::make()
                ->name('title')
                ->label('Titolo')
                ->translatable(),

            Input::make()
                ->name('subtitle')
                ->label('Sottotitolo')
                ->translatable(),

            Browser::make()
                ->name('event')
                ->label('Evento')
                ->modules(['events'])
                ->max(1)
                ->note('Evento a cui è collegata l\'iscrizione (salvato sulla Subscription).'),
        ]);
    }
}
