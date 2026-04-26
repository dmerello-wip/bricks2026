<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/**
 * Raw Twill media model, as returned via the medias() relationship.
 */
#[OA\Schema(
    schema: 'TwillMedia',
    required: ['id', 'uuid', 'filename'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'filename', type: 'string'),
        new OA\Property(property: 'width', type: 'integer', nullable: true),
        new OA\Property(property: 'height', type: 'integer', nullable: true),
        new OA\Property(property: 'size', type: 'integer'),
        new OA\Property(property: 'mime_type', type: 'string'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'pivot',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'role', type: 'string'),
                new OA\Property(property: 'crop', type: 'string'),
            ]
        ),
    ]
)]
class TwillMedia {}
