<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\NestedModuleController as BaseModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Services\Forms\Form;
use App\Twill\Concerns\HasBlockPreview;
use App\Twill\Fieldsets\SeoFieldset;

class PageController extends BaseModuleController
{
    use HasBlockPreview;

    protected $moduleName = 'pages';

    protected $showOnlyParentItemsInBrowsers = true;

    protected $nestedItemsDepth = 1;

    protected function setUpController(): void
    {
        $this->setPermalinkBase('');
        $this->enableReorder();
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->add(
            BlockEditor::make()->blocks([
                'hero',
                'abstract',
                'paragraph',
                'cardslist',
                'gallery',
                'download',
                'matrix',
                'video',
            ])
        );

        $form->addFieldset(SeoFieldset::make());

        return $form;
    }
}
