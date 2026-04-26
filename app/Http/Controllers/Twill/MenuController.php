<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Services\Listings\Columns\Link;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

class MenuController extends BaseModuleController
{   
    protected $moduleName = 'menus';
    protected function setUpController(): void
    {
        $this->disablePermalink();
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->add(
            Link::make()
                ->field('manage_items_link')
                ->title('Voci Menu')
                ->content('Gestisci voci')
                ->url(fn($item) => route('twill.menuitems.index', [
                    'filter' => json_encode(['menu_id' => $item->id])
                ]))
        );

        return $table;
    }
}
