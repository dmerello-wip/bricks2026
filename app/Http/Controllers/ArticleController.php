<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Services\SeoService;
use App\Services\TwillBlockService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(
        string $locale,
        string $prefix,
        string $categorySlug,
        CategoryRepository $categoryRepository
    ): Response|RedirectResponse {
        $category = $categoryRepository->forSlug($categorySlug);

        if (! $category || ! $category->published) {
            abort(404);
        }

        // Redirect if the slug doesn't match the active slug for the current locale
        // (handles cross-locale slugs and changed slugs)
        $activeSlug = $category->getSlug();
        if (! $activeSlug) {
            abort(404);
        }

        if ($activeSlug !== $categorySlug) {
            return redirect()->route('article-list', [
                'locale' => app()->getLocale(),
                'prefix' => trans('routes.articles'),
                'categorySlug' => $activeSlug,
            ], 301);
        }

        $articles = Article::query()
            ->where('published', true)
            ->whereHas('relatedItems', function ($query) use ($category) {
                $query->where('related_id', $category->id)
                    ->where('related_type', $category->getMorphClass())
                    ->where('browser_name', 'categories');
            })
            ->with(['translations', 'slugs'])
            ->latest()
            ->paginate(12);

        $currentLocale = app()->getLocale();
        $routePrefix = trans('routes.articles');

        $articlesList = $articles->through(fn (Article $article) => [
            'id' => $article->id,
            'title' => $article->title,
            'description' => $article->description,
            'created_at' => $article->created_at->toDateString(),
            'url' => route('article', [
                'locale' => $currentLocale,
                'prefix' => $routePrefix,
                'categorySlug' => $categorySlug,
                'slug' => $article->getSlug(),
            ]),
        ]);

        return Inertia::render('Article/List', [
            'category' => [
                'title' => $category->title,
                'description' => $category->description,
            ],
            'articles' => $articlesList,
            'seo' => [
                'title' => $category->title,
                'description' => $category->description,
                'canonical' => request()->url(),
                'og_title' => $category->title,
                'og_description' => $category->description,
                'og_image' => null,
                'no_index' => false,
                'alternates' => $this->resolveCategoryAlternates($category),
            ],
        ]);
    }

    public function show(
        string $locale,
        string $prefix,
        string $categorySlug,
        string $slug,
        ArticleRepository $repository,
        TwillBlockService $blockService,
        SeoService $seoService
    ): Response|RedirectResponse {
        $article = $repository->forSlug($slug);

        if (! $article || ! $article->published) {
            abort(404);
        }

        // Redirect if the article slug has changed (slug history)
        $activeSlug = $article->getSlug();
        if ($activeSlug !== $slug) {
            return redirect()->route('article', [
                'locale' => app()->getLocale(),
                'prefix' => trans('routes.articles'),
                'categorySlug' => $categorySlug,
                'slug' => $activeSlug,
            ], 301);
        }

        // Validate the category slug matches one of the article's categories
        $categories = $article->getRelated('categories')->loadMissing('slugs');
        // loadMissing('slugs') risolve l'N+1 caricando tutti gli slug delle categorie in un'unica query aggiuntiva, indipendentemente da quante categorie ha l'articolo.
        $matchingCategory = $categories->first(
            fn ($cat) => $cat->getSlug() === $categorySlug
        );

        if (! $matchingCategory) {
            if ($categories->isEmpty()) {
                abort(404);
            }

            // Redirect to the canonical URL using the first related category
            return redirect()->route('article', [
                'locale' => app()->getLocale(),
                'prefix' => trans('routes.articles'),
                'categorySlug' => $categories->first()->getSlug(),
                'slug' => $activeSlug,
            ], 301);
        }

        $article->load('seoData', 'medias', 'translations');

        $rawBlocks = $article->blocks()
            ->whereNull('parent_id')
            ->with(['children.medias', 'medias'])
            ->get();
        $blocks = $blockService->formatBlocks($rawBlocks);

        $seo = $seoService->resolve($article);
        $seo['alternates'] = $this->resolveArticleAlternates($article, $matchingCategory);


        return Inertia::render('Article/Show', [
            'article' => $article->toArray(),
            'blocks' => $blocks,
            'categoryName' => $matchingCategory->title,
            'seo' => $seo,
        ]);
    }

    /**
     * Build hreflang alternate URLs for a category listing page, one per supported locale.
     *
     * @return array<string, string>
     */
    private function resolveCategoryAlternates(Category $category): array
    {
        $alternates = [];

        $category->load('slugs', 'translations');

        $activeTranslationLocales = $category->translations
            ->where('active', true)
            ->pluck('locale')
            ->flip();

        $categorySlugs = $category->slugs
            ->where('active', true)
            ->pluck('slug', 'locale');

        foreach (array_keys(config('app.supported_locales')) as $locale) {
            if (! $activeTranslationLocales->has($locale) || ! isset($categorySlugs[$locale])) {
                continue;
            }

            $modulePrefix = trans('routes.articles', [], $locale);
            $alternates[$locale] = url("/{$locale}/{$modulePrefix}/{$categorySlugs[$locale]}");
        }

        $fallback = config('translatable.fallback_locale', 'en');

        if (isset($alternates[$fallback])) {
            $alternates['x-default'] = $alternates[$fallback];
        }

        return $alternates;
    }

    /**
     * Build hreflang alternate URLs for an article, including the localized module
     * prefix and the category slug for each supported locale.
     *
     * @return array<string, string>
     */
    private function resolveArticleAlternates(Article $article, Category $matchingCategory): array
    {
        $alternates = [];

        $matchingCategory->loadMissing('slugs');
        $activeTranslationLocales = $article->translations
            ->where('active', true)
            ->pluck('locale')
            ->flip();

        $articleSlugs = $article->slugs
            ->where('active', true)
            ->pluck('slug', 'locale');

        $categorySlugs = $matchingCategory->slugs
            ->where('active', true)
            ->pluck('slug', 'locale');

        foreach (array_keys(config('app.supported_locales')) as $locale) {
            if (
                ! $activeTranslationLocales->has($locale)
                || ! isset($articleSlugs[$locale])
                || ! isset($categorySlugs[$locale])
            ) {
                continue;
            }

            $modulePrefix = trans('routes.articles', [], $locale);
            $alternates[$locale] = url("/{$locale}/{$modulePrefix}/{$categorySlugs[$locale]}/{$articleSlugs[$locale]}");
        }

        $fallback = config('translatable.fallback_locale', 'en');

        if (isset($alternates[$fallback])) {
            $alternates['x-default'] = $alternates[$fallback];
        }

        return $alternates;
    }
}
