<?php

namespace App\Http\Controllers;

use App\Models\Homepage;
use App\Services\SeoService;
use App\Services\TwillBlockService;
use Inertia\Inertia;
use Inertia\Response;
use OpenApi\Attributes as OA;

class HomepageController extends Controller
{
    #[OA\Get(
        path: '/{locale}',
        summary: 'Homepage',
        tags: ['Pages'],
        parameters: [
            new OA\Parameter(name: 'locale', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'en')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Homepage rendered via Inertia.js',
                content: new OA\JsonContent(
                    required: ['page', 'blocks', 'seo'],
                    properties: [
                        new OA\Property(property: 'page', ref: '#/components/schemas/HomepageModel'),
                        new OA\Property(property: 'blocks', type: 'array', items: new OA\Items(ref: '#/components/schemas/Block')),
                        new OA\Property(property: 'seo', ref: '#/components/schemas/SeoData'),
                    ]
                )
            ),
            new OA\Response(response: 503, description: 'Homepage not published'),
        ]
    )]
    public function __invoke(TwillBlockService $blockService, SeoService $seoService): Response
    {
        $homepage = Homepage::first();

        if (! $homepage || ! $homepage->published) {
            abort(503);
        }

        $homepage->load('seoData', 'medias');

        $rawBlocks = $homepage->blocks()
            ->whereNull('parent_id')
            ->with(['children.medias', 'medias'])
            ->get();

        return Inertia::render('Homepage', [
            'page' => $homepage->toArray(),
            'blocks' => $blockService->formatBlocks($rawBlocks),
            'seo' => $seoService->resolve($homepage),
        ]);
    }
}
