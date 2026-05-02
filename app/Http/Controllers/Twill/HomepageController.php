<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\SingletonModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use App\Twill\Concerns\HasBlockPreview;
use App\Twill\Fieldsets\SeoFieldset;

class HomepageController extends SingletonModuleController
{
    use HasBlockPreview;

    protected $moduleName = 'homepages';

    protected function setUpController(): void
    {
        $this->disablePermalink();
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->add(
            Input::make()->name('title')->label('Title')->translatable()
        );

        $form->add(
            BlockEditor::make()->blocks(['hero', 'abstract', 'paragraph', 'download', 'gallery', 'matrix', 'video', 'subscriptionform'])
        );

        $form->addFieldset(SeoFieldset::make());

        return $form;
    }
}
