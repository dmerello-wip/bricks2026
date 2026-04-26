<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleFiles;
use A17\Twill\Repositories\Behaviors\HandleNesting;
use A17\Twill\Repositories\Behaviors\HandleTranslations;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\Menu;
use App\Models\Menuitem;
use Illuminate\Support\Facades\Cache;

class MenuitemRepository extends ModuleRepository
{
    use HandleFiles, HandleNesting, HandleTranslations;

    protected $relatedBrowsers = [
        'related_content' => [
            'moduleName' => 'pages',
        ],
    ];

    public function __construct(Menuitem $model)
    {
        $this->model = $model;
    }

    public function prepareFieldsBeforeCreate(array $fields): array
    {
        $fields = parent::prepareFieldsBeforeCreate($fields);

        $parentId = (int) ($fields['parent_id'] ?? 0);
        $menuId = (int) ($fields['menu_id'] ?? $fields['browsers']['menu'][0]['id'] ?? 0);

        if (! $parentId && $referer = request()->header('referer')) {
            parse_str((string) parse_url($referer, PHP_URL_QUERY), $query);
            $parentId = (int) ($query['parent_id'] ?? 0);
        }

        if (! $menuId) {
            $menuId = (int) (request()->get('menu_id') ?? 0);
        }

        if (! $menuId && $referer = request()->header('referer')) {
            parse_str((string) parse_url($referer, PHP_URL_QUERY), $query);
            $menuId = (int) ($query['menu_id'] ?? 0);

            if (! $menuId && isset($query['filter'])) {
                $filter = json_decode($query['filter'], true);
                $menuId = (int) ($filter['menu_id'] ?? 0);
            }
        }

        if ($parentId) {
            $fields['parent_id'] = $parentId;

            if (! $menuId) {
                $parent = $this->model->find($parentId);
                if ($parent) {
                    $menuId = (int) $parent->menu_id;
                }
            }
        }

        if ($menuId) {
            $fields['menu_id'] = $menuId;
        }

        return $fields;
    }

    public function prepareFieldsBeforeUpdate($object, array $fields): array
    {
        $fields = parent::prepareFieldsBeforeUpdate($object, $fields);

        $menuId = (int) ($fields['menu_id'] ?? $fields['browsers']['menu'][0]['id'] ?? 0);

        if ($menuId) {
            $fields['menu_id'] = $menuId;
        }

        return $fields;
    }

    /**
     * Fix Nested Menu Twill data for Vue in admin
     */
    public function getFormFields($object): array
    {
        $fields = parent::getFormFields($object);

        if (! $object->exists) {
            $locales = array_keys(config('app.supported_locales'));
            $emptyLocales = array_fill_keys($locales, '');
            foreach ($object->translatedAttributes as $attr) {
                $fields['translations'][$attr] = $emptyLocales;
            }
        }

        $menuId = $object->menu_id ?? (int) request()->get('menu_id');

        if ($menuId) {
            $fields['menu_id'] = (string) $menuId;
            $menu = Menu::find($menuId);
            if ($menu) {
                $editUrl = route('twill.menus.edit', ['menu' => $menu->id]);
                $fields['browsers']['menu'] = [[
                    'id' => $menu->id,
                    'name' => $menu->title,
                    'edit' => $editUrl,
                    'endpoint' => $editUrl,
                    'endpointType' => 'menus',
                ]];
            }
        }

        // DZ: why this:
        // We retrieve and provide data to Vue to render the browser field in update admin form.
        // Probably Twill can't find the page class due to the nested module Menuitem.
        $fields['browsers']['related_content'] = $object->getRelated('related_content')->map(function ($item) {
            if ($item instanceof \App\Models\Page) {
                $editUrl = route('twill.pages.edit', ['page' => $item->id]);
                $endpointType = 'pages';
            } else {
                $editUrl = route('twill.categories.edit', ['category' => $item->id]);
                $endpointType = 'categories';
            }

            return [
                'id' => $item->id,
                'name' => $item->title,
                'edit' => $editUrl,
                'endpoint' => $editUrl,
                'endpointType' => $endpointType,
            ];
        })->toArray();

        return $fields;
    }

    // Cache query results for menu tree retrieval
    public function getMenuTree(int $menuId, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return Cache::rememberForever("menu:{$menuId}:{$locale}", function () use ($menuId, $locale) {
            $items = $this->model
                ->published()
                ->with(['translations', 'relatedContent.related.slugs', 'relatedContent.related.translations'])
                ->where('menu_id', $menuId)
                ->orderBy('position')
                ->get();

            return $this->buildTree($items->groupBy('parent_id'), $locale);
        });
    }

    // Invalidate all cached locales for the item's menu on save/delete
    public function afterSave($object, array $fields): void
    {
        parent::afterSave($object, $fields);

        $parentId = (int) ($fields['parent_id'] ?? 0);

        if ($parentId && (int) $object->parent_id !== $parentId) {
            $parent = $this->model->find($parentId);
            if ($parent) {
                $object->appendToNode($parent)->save();
            }
        }

        if ($object->menu_id) {
            session(['menuitem.last_menu_id' => $object->menu_id]);
        }

        $this->forgetMenuCache($object->menu_id);
    }

    public function afterDelete($object): void
    {
        parent::afterDelete($object);
        $this->forgetMenuCache($object->menu_id);
    }

    private function forgetMenuCache(int $menuId): void
    {
        foreach (array_keys(config('app.supported_locales')) as $locale) {
            Cache::forget("menu:{$menuId}:{$locale}");
        }
    }

    private function buildTree($grouped, string $locale, $parentId = null): array
    {
        return $grouped->get($parentId, collect())
            ->map(fn ($item) => $this->buildNode($item, $grouped, $locale))
            ->filter(fn ($node) => ! empty($node['title']) && ! empty($node['url']))
            ->values()
            ->all();
    }

    private function buildNode($item, $grouped, string $locale): array
    {
        $node = [
            'id' => $item->id,
            'title' => $item->title,
            'type' => $item->type,
            'url' => $this->getItemUrl($item, $locale),
            'target' => $item->target ?? '_self',
        ];

        $children = $this->buildTree($grouped, $locale, $item->id);
        if ($children) {
            $node['children'] = $children;
        }

        return $node;
    }

    private function getItemUrl($item, string $locale): ?string
    {
        return match ($item->type) {
            'external' => $item->external_url,
            'internal' => (function () use ($item, $locale): ?string {
                $related = $item->relatedContent->first()?->related;
                if (! $related) {
                    return null;
                }
                if ($related instanceof \App\Models\Page) {
                    return '/'.$locale.'/'.$related->slug;
                }
                if ($related instanceof \App\Models\Category) {
                    return '/'.$locale.'/'.trans('routes.articles', [], $locale).'/'.$related->slug;
                }

                return null;
            })(),
            default => null,
        };
    }
}
