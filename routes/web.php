<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\ImageCropperController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// ------------------------
// static routes
// ------------------------

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

$cachePath = env('IMAGE_CACHE_PATH', 'storage/img/crops');
Route::get($cachePath.'/{path}', [ImageCropperController::class, 'processImage'])
    ->where('path', '.*');

// Redirect root to default locale
Route::get('/', fn () => redirect('/'.config('app.locale')));

// ------------------------
// localized routes
// ------------------------

$localePattern = implode('|', array_keys(config('app.supported_locales')));

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => $localePattern],
    'middleware' => \App\Http\Middleware\SetLocale::class,
], function () {

    Route::get('welcome', function () {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    })->name('welcome');

    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::get('/', HomepageController::class)->name('home');

    // ------------------------
    // module routes (add before the catch-all)
    // ------------------------
    //   1. Add prefix translations in lang/it/routes.php and lang/en/routes.php
    //
    //   2. Register the route with the macro (one line per module):
    //      eg. Route::localizedModule('projects', ProjectController::class, 'project');
    //
    //   3. To build links to a module item (in controllers, Inertia props, etc.), use the route() helper.
    //      eg. route('project', ['locale' => app()->getLocale(), 'prefix' => trans('routes.projects'), 'slug' => $project->slug])
    //        → /it/progetti/torre-eiffel  (locale: it)
    //        → /en/projects/eiffel-tower  (locale: en)
    //
    //   4. In the Twill module controller, override getLocalizedPermalinkBase() to have a correct URL in admin UI.

    Route::localizedCategoryIndex('articles', ArticleController::class, 'article-list');
    Route::localizedCategorizedArticle('articles', ArticleController::class, 'article');

    // Catch-all for pages (must remain last)
    Route::get('/{slug}', [PageController::class, 'show'])
        ->where('slug', '.*')
        ->name('page');

});

require __DIR__.'/settings.php';
