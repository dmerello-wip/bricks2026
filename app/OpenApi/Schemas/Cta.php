<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/**
 * CTA block content as returned by TwillBlockService::formatCtaBlock().
 */
#[OA\Schema(
    schema: 'CtaContent',
    required: ['cta_label', 'cta_style', 'cta_type', 'cta_link', 'cta_target_blank'],
    properties: [
        new OA\Property(property: 'cta_label', type: 'string', nullable: true),
        new OA\Property(property: 'cta_style', type: 'string', enum: ['primary', 'secondary']),
        new OA\Property(property: 'cta_type', type: 'string', enum: ['internal', 'external', 'download']),
        new OA\Property(property: 'cta_link', type: 'string', nullable: true),
        new OA\Property(property: 'cta_target_blank', type: 'boolean'),
        new OA\Property(property: 'cta_dl_link', type: 'string', nullable: true),
        new OA\Property(property: 'cta_dl_filename', type: 'string', nullable: true),
    ]
)]

/**
 * A Block with its content typed as CtaContent.
 */
#[OA\Schema(
    schema: 'CtaBlock',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/Block'),
        new OA\Schema(
            properties: [
                new OA\Property(property: 'content', ref: '#/components/schemas/CtaContent'),
            ]
        ),
    ]
)]
class Cta {}
