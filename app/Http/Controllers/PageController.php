<?php

namespace App\Http\Controllers;

use App\Repositories\PageRepository;
use App\Services\SeoService;
use App\Services\TwillBlockService;
use Inertia\Inertia;
use OpenApi\Attributes as OA;

class PageController extends Controller
{
    #[OA\Get(
        path: '/{locale}/{slug}',
        summary: 'CMS Page',
        tags: ['Pages'],
        parameters: [
            new OA\Parameter(name: 'locale', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'it')),
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'about-us')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Page rendered via Inertia.js',
                content: new OA\JsonContent(
                    required: ['page', 'blocks', 'seo'],
                    properties: [
                        new OA\Property(property: 'page', ref: '#/components/schemas/PageModel'),
                        new OA\Property(property: 'blocks', type: 'array', items: new OA\Items(ref: '#/components/schemas/Block')),
                        new OA\Property(property: 'seo', ref: '#/components/schemas/SeoData'),
                    ]
                )
            ),
            new OA\Response(response: 301, description: 'Redirect to canonical slug'),
            new OA\Response(response: 404, description: 'Page not found or not published'),
        ]
    )]
    public function show(
        string $locale,
        string $slug,
        PageRepository $repository,
        TwillBlockService $blockService,
        SeoService $seoService
    ) {
        $page = $repository->forSlug($slug);

        if (! $page || ! $page->published) {
            abort(404);
        }

        $activeSlug = $page->getSlug();
        if ($activeSlug !== $slug) {
            return redirect()->route('page', ['locale' => app()->getLocale(), 'slug' => $activeSlug], 301);
        }

        $page->load('seoData', 'medias');

        $rawBlocks = $page->blocks()
            ->whereNull('parent_id')
            ->with(['children.medias', 'medias'])
            ->get();
        $blocks = $blockService->formatBlocks($rawBlocks);

        return Inertia::render('Page', [
            'page' => $page->toArray(),
            'blocks' => $blocks,
            'seo' => $seoService->resolve($page),
        ]);
    }
}
