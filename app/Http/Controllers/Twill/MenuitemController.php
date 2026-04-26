<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\NestedModuleController as BaseModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\Browser;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Fieldset;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Option;
use A17\Twill\Services\Forms\Options;
use A17\Twill\Services\Listings\Columns\Link;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use App\Http\Controllers\Twill\Concerns\HasSaveAndCloseAsDefault;

class MenuitemController extends BaseModuleController
{
    use HasSaveAndCloseAsDefault;

    protected $moduleName = 'menuitems';

    protected $showOnlyParentItemsInBrowsers = true;

    protected $nestedItemsDepth = 4;

    protected function setUpController(): void
    {
        $this->disablePermalink();
        $this->enableReorder();
        $this->enableSkipCreateModal();
    }

    private function getMenuIdFromRequest(): ?int
    {
        if ($menuId = request()->get('menu_id')) {
            return (int) $menuId;
        }

        $filter = json_decode(request()->get('filter'), true);

        return $filter['menu_id'] ?? null;
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->addFieldset(
            Fieldset::make()
                ->title('Menuitem content')
                ->id('general')
                ->fields([
                    Browser::make()
                        ->name('menu')
                        ->label('Menu')
                        ->modules(['menu'])
                        ->max(1)
                        ->required(),

                    Select::make()
                        ->name('type')
                        ->label('Tipo di Link')
                        ->options(
                            Options::make([
                                Option::make('internal', 'Link Interno'),
                                Option::make('external', 'Link Esterno'),
                            ])
                        )
                        ->default('internal'),

                    Browser::make()
                        ->label('Contenuto Interno')
                        ->modules(['pages', 'categories'])
                        ->name('related_content')
                        ->max(1)
                        ->connectedTo('type', 'internal'),

                    Input::make()
                        ->name('external_url')
                        ->label('URL Esterno')
                        ->connectedTo('type', 'external')
                        ->note('Inserisci URL per link esterno'),

                    Select::make()
                        ->name('target')
                        ->label('Apri in')
                        ->options(
                            Options::make([
                                Option::make('_self', 'Stessa finestra'),
                                Option::make('_blank', 'Nuova finestra'),
                            ])
                        )
                        ->default('_self'),
                ])
        );

        return $form;
    }

    protected function getIndexItems($scopes = [], $forcePagination = false)
    {
        $menuId = $this->getMenuIdFromRequest();

        if ($menuId) {
            $scopes['menu_id'] = $menuId;
        }

        return parent::getIndexItems($scopes, $forcePagination);
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();
        $table->add(
            Text::make()
                ->field('menu.title')
                ->title('Menu')
                ->customRender(function ($item) {
                    $menuName = $item->menu ? $item->menu->title : '--';

                    return 'menu: '.$menuName;
                })
        );

        $table->add(
            Link::make()
                ->field('create_child')
                ->title('Aggiungi figlio')
                ->url(fn ($item) => ($item->menu_id && $item->depth < $this->nestedItemsDepth)
                    ? route('twill.menuitems.create', [
                        'menu_id' => $item->menu_id,
                        'parent_id' => $item->id,
                    ])
                    : null
                )
                ->content(fn ($item) => ($item->menu_id && $item->depth < $this->nestedItemsDepth) ? '+ Crea sotto voce' : '')
        );

        return $table;
    }

    protected function getIndexUrls($moduleName, $routePrefix): array
    {
        $urls = parent::getIndexUrls($moduleName, $routePrefix);

        $menuId = $this->getMenuIdFromRequest();
        if ($menuId && isset($urls['createUrl'])) {
            $urls['createUrl'] .= '?'.http_build_query(['menu_id' => $menuId]);
        }

        return $urls;
    }

    protected function getBackLink($fallback = null, $params = [])
    {
        $menuId = $this->getMenuIdFromRequest() ?? session('menuitem.last_menu_id');

        return route('twill.menuitems.index', [
            'filter' => json_encode(['menu_id' => $menuId]),
        ]);
    }
}
