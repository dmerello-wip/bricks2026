<?php

namespace App\Http\Controllers;

use App\Repositories\EventRepository;
use App\Services\SeoService;
use App\Services\TwillBlockService;
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
        TwillBlockService $blockService,
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

        $rawBlocks = $event->blocks()
            ->whereNull('parent_id')
            ->with(['children.medias', 'medias'])
            ->get();
        $blocks = $blockService->formatBlocks($rawBlocks);

        return Inertia::render('Event/Show', [
            'event' => $event->toArray(),
            'blocks' => $blocks,
            'seo' => $seoService->resolve($event),
        ]);
    }
}
