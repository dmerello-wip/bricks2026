<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Services\Forms\Fields\Browser;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Listings\TableColumns;
use App\Twill\Fieldsets\SeoFieldset;

class ArticleController extends BaseModuleController
{
    protected $moduleName = 'articles';

    protected function setUpController(): void
    {
        $this->setPermalinkBase('');
    }

    protected function getLocalizedPermalinkBase(): array
    {
        return [
            'it' => trans('routes.articles', [], 'it').'/{category}',
            // 'en' => trans('routes.articles', [], 'en').'/{category}',
        ];
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->add(
            Browser::make()
                ->name('categories')
                ->label('Categorie')
                ->modules(['categories'])
                ->required(true)
        );

        $form->add(
            BlockEditor::make()->blocks([
                'hero',
                'paragraph',
                'video',
            ])
        );

        $form->addFieldset(SeoFieldset::make());

        return $form;
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        return $table;
    }
}
