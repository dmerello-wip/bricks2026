<?php

namespace App\Providers;

use A17\Twill\Facades\TwillNavigation;
use A17\Twill\View\Components\Navigation\NavigationLink;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \A17\Twill\Http\Controllers\Admin\BlocksController::class,
            \App\Http\Controllers\Admin\BlocksController::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::defaults(['locale' => config('app.locale')]);

        $this->configureDefaults();
        $this->registerRouteMacros();
        $this->registerTwillCustomCss();
        TwillNavigation::addLink(
            NavigationLink::make()->forSingleton('homepage')->title('Homepage')
        );
        TwillNavigation::addLink(
            NavigationLink::make()->forModule('pages')
        );
        TwillNavigation::addLink(
            NavigationLink::make()->forModule('articles')
        );
        TwillNavigation::addLink(
            NavigationLink::make()->forModule('categories')
        );
        TwillNavigation::addLink(
            NavigationLink::make()->forModule('menus')
        );
        TwillNavigation::addLink(
            NavigationLink::make()->forModule('subscriptions')->title('Iscrizioni')
        );
        TwillNavigation::addLink(
            NavigationLink::make()->forSingleton('seoDefault')->title('SEO Defaults')
        );

        // Dz: Needed to let Twill understand relations in Nested items (as Menuitem with Pages)
        Relation::morphMap([
            'pages' => \App\Models\Page::class,
            'menuitems' => \App\Models\Menuitem::class,
            'homepages' => \App\Models\Homepage::class,
            'seoDefaults' => \App\Models\SeoDefault::class,
            'categories' => \App\Models\Category::class,
        ]);
    }

    protected function registerTwillCustomCss(): void
    {
        View::composer('twill::*', function ($view): void {
            $view->getFactory()->startPush('extra_css');
            echo '<link rel="stylesheet" href="'.Vite::asset('resources/css/twill-admin-addons.css').'">';
            $view->getFactory()->stopPush();
        });
    }

    protected function registerRouteMacros(): void
    {
        // Usage inside the localized route prefix group in web.php:
        //   Route::localizedModule('projects', ProjectController::class, 'project');
        // → matches /{locale}/progetti/{slug} and /{locale}/projects/{slug}
        // → URL generation: route('project', ['locale' => app()->getLocale(), 'prefix' => trans('routes.projects'), 'slug' => $slug])
        Route::macro('localizedModule', function (string $routeKey, string $controller, string $routeName, string $slugPattern = '[^/]+'): void {
            $prefixes = collect(array_keys(config('app.supported_locales')))
                ->map(fn ($locale) => trans("routes.$routeKey", [], $locale))
                ->unique()
                ->join('|');

            Route::get('{prefix}/{slug}', [$controller, 'show'])
                ->where('prefix', $prefixes)
                ->where('slug', $slugPattern)
                ->name($routeName);
        });

        // Usage inside the localized route prefix group in web.php:
        //   Route::localizedCategorizedArticle('articles', ArticleController::class, 'article');
        // → matches /{locale}/articoli/{category-slug}/{slug} and /{locale}/articles/{category-slug}/{slug}
        // → URL generation: route('article', ['locale' => ..., 'prefix' => trans('routes.articles'), 'categorySlug' => $cat->slug, 'slug' => $slug])
        Route::macro('localizedCategorizedArticle', function (string $routeKey, string $controller, string $routeName, string $slugPattern = '[^/]+'): void {
            $prefixes = collect(array_keys(config('app.supported_locales')))
                ->map(fn ($locale) => trans("routes.$routeKey", [], $locale))
                ->unique()
                ->join('|');

            Route::get('{prefix}/{categorySlug}/{slug}', [$controller, 'show'])
                ->where('prefix', $prefixes)
                ->where('categorySlug', '[^/]+')
                ->where('slug', $slugPattern)
                ->name($routeName);
        });

        // Usage inside the localized route prefix group in web.php:
        //   Route::localizedCategoryIndex('articles', ArticleController::class, 'article-list');
        // → matches /{locale}/novita/{category-slug} and /{locale}/news/{category-slug}
        // → URL generation: route('article-list', ['locale' => ..., 'prefix' => trans('routes.articles'), 'categorySlug' => $cat->getSlug()])
        Route::macro('localizedCategoryIndex', function (string $routeKey, string $controller, string $routeName): void {
            $prefixes = collect(array_keys(config('app.supported_locales')))
                ->map(fn ($locale) => trans("routes.$routeKey", [], $locale))
                ->unique()
                ->join('|');

            Route::get('{prefix}/{categorySlug}', [$controller, 'index'])
                ->where('prefix', $prefixes)
                ->where('categorySlug', '[^/]+')
                ->name($routeName);
        });
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
