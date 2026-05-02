<?php

namespace App\Http\Controllers;

use App\Repositories\EventRepository;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function show(
        string $locale,
        string $prefix,
        string $slug,
        EventRepository $repository,
        SeoService $seoService
    ): Response|RedirectResponse {
        $event = $repository->forSlug($slug);

        if (! $event || ! $event->published) {
            abort(404);
        }

        $activeSlug = $event->getSlug();
        if ($activeSlug !== $slug) {
            return redirect()->route('event', [
                'locale' => app()->getLocale(),
                'prefix' => trans('routes.events'),
                'slug' => $activeSlug,
            ], 301);
        }

        $event->load('seoData', 'medias', 'translations');

        return Inertia::render('Event/Show', [
            'event' => $event->toArray(),
            'seo' => $seoService->resolve($event),
        ]);
    }
}
