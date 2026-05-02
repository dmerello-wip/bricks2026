<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\DatePicker;
use A17\Twill\Services\Forms\Fields\Files;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;

class SubscriptionController extends BaseModuleController
{
    protected $moduleName = 'subscriptions';

    protected function setUpController(): void
    {
        $this->disablePermalink();
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->add(
            Input::make()
                ->name('evento')
                ->label('Evento')
        );

        $form->add(
            Input::make()
                ->name('band')
                ->label('Nome della Band')
        );

        $form->add(
            Input::make()
                ->name('nr_componenti')
                ->label('Numero dei componenti')
                ->type('number')
        );

        $form->add(
            Input::make()
                ->name('eta_media')
                ->label('Età media')
        );

        $form->add(
            Input::make()
                ->name('citta')
                ->label('Città')
        );

        $form->add(
            Input::make()
                ->name('genere')
                ->label('Genere musicale')
        );

        $form->add(
            Input::make()
                ->name('durata')
                ->label('Durata live set (minuti)')
                ->type('number')
        );

        $form->add(
            Input::make()
                ->name('referente')
                ->label('Nome e Cognome referente')
        );

        $form->add(
            Input::make()
                ->name('telefono')
                ->label('Telefono referente')
        );

        $form->add(
            Input::make()
                ->name('email')
                ->label('Email referente')
                ->type('email')
        );

        $form->add(
            Files::make()
                ->name('video_file')
                ->label('Video file')
                ->note('File video caricato dal PC (max 100MB).')
                ->max(1)
        );

        $form->add(
            Input::make()
                ->name('video_link')
                ->label('Link al video')
                ->note('In alternativa al file: link a YouTube, Vimeo, Drive, WeTransfer, ecc.')
        );

        $form->add(
            Checkbox::make()
                ->name('privacy')
                ->label('Consenso privacy')
        );

        $form->add(
            DatePicker::make()
                ->name('data_iscrizione')
                ->label('Data iscrizione')
        );

        return $form;
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->add(
            Text::make()
                ->field('evento')
                ->title('Evento')
                ->sortable()
        );

        $table->add(
            Text::make()
                ->field('band')
                ->title('Band')
                ->sortable()
                ->customRender(fn ($model) => "<strong>Band:</strong> {$model->band}<br>".
                    "<strong>Componenti:</strong> {$model->nr_componenti}<br>".
                    "<strong>Durata:</strong> {$model->durata} min<br>".
                    "<strong>Età media:</strong> {$model->eta_media}<br>".
                    "<strong>Genere:</strong> {$model->genere}")
        );
        $table->add(
            Text::make()
                ->field('referente')
                ->title('Referente')
                ->customRender(fn ($model) => "<strong>Referente:</strong> {$model->referente}<br>".
                    "<strong>Email:</strong> {$model->email}<br>".
                    "<strong>Telefono:</strong> {$model->telefono}<br>".
                    "<strong>Città:</strong> {$model->citta}")
        );

        $table->add(
            Text::make()
                ->field('video_file_url')
                ->title('Video File')
                ->customRender(function ($model) {
                    if ($model->video_file_url) {
                        return "<a href=\"{$model->video_file_url}\" target=\"_blank\">Scarica video</a>";
                    }
                    if ($model->video_link) {
                        return "<a href=\"{$model->video_link}\" target=\"_blank\">Apri video</a>";
                    }

                    return 'N/A';
                })
        );

        $table->add(
            Text::make()
                ->field('data_iscrizione')
                ->title('Data iscrizione')
                ->sortable()
        );

        return $table;
    }
}
