<?php

namespace App\Services;

use A17\Twill\Models\Contracts\TwillModelContract;
use App\Models\SeoDefault;

class SeoService
{
    private ?array $defaults = null;

    public function resolve(TwillModelContract $model): array
    {
        $locale = app()->getLocale();
        $defaults = $this->getDefaults($locale);
        $seo = $model->seoData?->getForLocale($locale) ?? [];

        return [
            'title' => $seo['seo_title'] ?? $model->title ?? $defaults['default_title'] ?? null,
            'description' => $seo['seo_description'] ?? $defaults['default_description'] ?? null,
            'canonical' => $seo['canonical'] ?? request()->url(),
            'og_title' => $seo['og_title'] ?? $seo['seo_title'] ?? $model->title ?? $defaults['default_og_title'] ?? null,
            'og_description' => $seo['og_description'] ?? $seo['seo_description'] ?? $defaults['default_og_description'] ?? null,
            'og_image' => $model->image('seo_og_image', 'default', [], true) ?? $defaults['default_og_image'] ?? null,
            'no_index' => $model->seoData?->no_index ?? false,
            'alternates' => $this->resolveAlternates($model),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function resolveAlternates(TwillModelContract $model): array
    {
        $alternates = [];
        $hasSlug = method_exists($model, 'slugs');

        $activeTranslationLocales = $model->translations()
            ->where('active', true)
            ->pluck('locale')
            ->flip();

        $activeSlugs = $hasSlug
            ? $model->slugs()->where('active', true)->pluck('slug', 'locale')
            : collect();

        $currentLocale = app()->getLocale();

        foreach (array_keys(config('app.supported_locales')) as $locale) {
            if (! $activeTranslationLocales->has($locale)) {
                continue;
            }

            if ($hasSlug) {
                if (! isset($activeSlugs[$locale])) {
                    continue;
                }

                $alternates[$locale] = url("/{$locale}/{$activeSlugs[$locale]}");
            } else {
                $alternates[$locale] = preg_replace(
                    '#/'.preg_quote($currentLocale, '#').'(/|$)#',
                    '/'.$locale.'$1',
                    request()->url()
                );
            }
        }

        $fallback = config('translatable.fallback_locale', 'en');

        if (isset($alternates[$fallback])) {
            $alternates['x-default'] = $alternates[$fallback];
        }

        return $alternates;
    }

    /**
     * @return array{default_title: string|null, default_description: string|null, default_og_title: string|null, default_og_description: string|null, default_og_image: string|null}
     */
    private function getDefaults(string $locale): array
    {
        if ($this->defaults !== null) {
            return $this->defaults;
        }

        /** @var SeoDefault|null $record */
        $record = SeoDefault::with('medias')->first();

        if (! $record) {
            return $this->defaults = [];
        }

        $this->defaults = [
            'default_title' => $record->translateOrDefault($locale)->default_title ?? null,
            'default_description' => $record->translateOrDefault($locale)->default_description ?? null,
            'default_og_title' => $record->translateOrDefault($locale)->default_og_title ?? null,
            'default_og_description' => $record->translateOrDefault($locale)->default_og_description ?? null,
            'default_og_image' => $record->image('default_og_image', 'default', [], true) ?? null,
        ];

        return $this->defaults;
    }
}
