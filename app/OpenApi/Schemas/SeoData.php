<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/**
 * SEO metadata resolved by SeoService::resolve().
 */
#[OA\Schema(
    schema: 'SeoData',
    required: ['canonical', 'no_index', 'alternates'],
    properties: [
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'canonical', type: 'string'),
        new OA\Property(property: 'og_title', type: 'string', nullable: true),
        new OA\Property(property: 'og_description', type: 'string', nullable: true),
        new OA\Property(property: 'og_image', type: 'string', nullable: true),
        new OA\Property(property: 'no_index', type: 'boolean'),
        new OA\Property(
            property: 'alternates',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'string')
        ),
    ]
)]
class SeoData {}
