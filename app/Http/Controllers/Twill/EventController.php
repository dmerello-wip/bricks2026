<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Services\Forms\Fields\DatePicker;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Map as MapField;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use App\Models\Event;
use App\Twill\Fieldsets\SeoFieldset;

class EventController extends BaseModuleController
{
    protected $moduleName = 'events';

    protected function setUpController(): void
    {
        $this->setPermalinkBase('');
    }

    protected function getLocalizedPermalinkBase(): array
    {
        return [
            'it' => trans('routes.events', [], 'it'),
        ];
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->add(
            Input::make()
                ->name('description')
                ->label('Descrizione')
                ->type('textarea')
                ->translatable()
        );

        $form->add(
            DatePicker::make()
                ->name('data')
                ->label('Data')
                ->time24h()
        );

        $form->add(
            MapField::make()
                ->name('luogo')
                ->label('Luogo')
                ->saveExtendedData()
        );

        $form->add(
            BlockEditor::make()->blocks([
                'hero',
                'abstract',
                'paragraph',
                'cardslist',
                'gallery',
                'masonrygallery',
                'download',
                'matrix',
                'video',
                'subscriptionform',
            ])
        );

        $form->addFieldset(SeoFieldset::make());

        return $form;
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->add(
            Text::make()
                ->field('data')
                ->title('Data')
                ->sortable()
        );

        $table->add(
            Text::make()
                ->field('luogo')
                ->title('Luogo')
                ->customRender(fn (Event $event): string => is_array($event->luogo) ? ($event->luogo['address'] ?? '') : '')
        );

        return $table;
    }
}
