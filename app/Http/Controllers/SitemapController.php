<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Homepage;
use App\Models\Page;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), fn () => $this->build()->render());

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    private function build(): Sitemap
    {
        $sitemap = Sitemap::create();
        $locales = array_keys(config('app.supported_locales'));

        $this->addHomepage($sitemap, $locales);
        $this->addPages($sitemap, $locales);
        $this->addArticles($sitemap, $locales);

        return $sitemap;
    }

    private function addHomepage(Sitemap $sitemap, array $locales): void
    {
        $homepage = Homepage::with('seoData')->first();

        if (! $homepage || ! $homepage->published || $homepage->seoData?->no_index) {
            return;
        }

        $url = Url::create(url('/'))
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY);

        foreach ($locales as $locale) {
            $translationActive = $homepage->translations()
                ->where('locale', $locale)
                ->where('active', true)
                ->exists();

            if ($translationActive) {
                $url->addAlternate(url("/{$locale}"), $locale);
            }
        }

        $sitemap->add($url);
    }

    private function addArticles(Sitemap $sitemap, array $locales): void
    {
        $articles = Article::query()
            ->where('published', true)
            ->with(['seoData', 'slugs'])
            ->get();

        foreach ($articles as $article) {
            if ($article->seoData?->no_index) {
                continue;
            }

            $categories = $article->getRelated('categories')->loadMissing('slugs');

            if ($categories->isEmpty()) {
                continue;
            }

            $category = $categories->first();
            $primaryUrl = null;
            $alternates = [];

            foreach ($locales as $locale) {
                $articleSlug = $article->slugs
                    ->where('locale', $locale)
                    ->where('active', true)
                    ->first()
                    ?->slug;

                $categorySlug = $category->slugs
                    ->where('locale', $locale)
                    ->where('active', true)
                    ->first()
                    ?->slug;

                if (! $articleSlug || ! $categorySlug) {
                    continue;
                }

                $prefix = trans('routes.articles', [], $locale);
                $localeUrl = url("/{$locale}/{$prefix}/{$categorySlug}/{$articleSlug}");

                $primaryUrl ??= $localeUrl;
                $alternates[$locale] = $localeUrl;
            }

            if ($primaryUrl === null) {
                continue;
            }

            $url = Url::create($primaryUrl)
                ->setPriority(0.7)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

            foreach ($alternates as $locale => $altUrl) {
                $url->addAlternate($altUrl, $locale);
            }

            $sitemap->add($url);
        }
    }

    private function addPages(Sitemap $sitemap, array $locales): void
    {
        $pages = Page::query()
            ->where('published', true)
            ->with(['seoData', 'slugs'])
            ->get();

        foreach ($pages as $page) {
            if ($page->seoData?->no_index) {
                continue;
            }

            $primaryUrl = null;
            $alternates = [];

            foreach ($locales as $locale) {
                $slug = $page->slugs
                    ->where('locale', $locale)
                    ->where('active', true)
                    ->first()
                    ?->slug;

                if (! $slug) {
                    continue;
                }

                $localeUrl = url("/{$locale}/{$slug}");

                $primaryUrl ??= $localeUrl;
                $alternates[$locale] = $localeUrl;
            }

            if ($primaryUrl === null) {
                continue;
            }

            $url = Url::create($primaryUrl)
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

            foreach ($alternates as $locale => $altUrl) {
                $url->addAlternate($altUrl, $locale);
            }

            $sitemap->add($url);
        }
    }
}
