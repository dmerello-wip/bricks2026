<?php

namespace App\Http\Middleware;

use App\Repositories\MenuitemRepository;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'locale' => fn () => app()->getLocale(),
            'locales' => config('app.supported_locales'),
            'localizedURL' => fn () => request()->url(),
            'routePrefixes' => fn () => collect(config('translatable.locales'))
                ->mapWithKeys(fn (string $l) => [$l => trans('routes', [], $l)])
                ->toArray(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'menu' => fn () => [
                'primary' => app(MenuitemRepository::class)->getMenuTree(config('menu.primary_id', 1), app()->getLocale()),
                'footer' => app(MenuitemRepository::class)->getMenuTree(config('menu.footer_id', 2), app()->getLocale()),
            ],
        ];
    }
}
