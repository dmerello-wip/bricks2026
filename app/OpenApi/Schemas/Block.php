<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/**
 * Formatted block data returned by TwillBlockService::formatBlock().
 */
#[OA\Schema(
    schema: 'Block',
    required: ['id', 'type', 'content'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'parent_id', type: 'integer', nullable: true),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'content', type: 'object', additionalProperties: new OA\AdditionalProperties()),
        new OA\Property(
            property: 'medias',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TwillMedia')
        ),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Block')
        ),
        new OA\Property(
            property: 'images',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'object',
                additionalProperties: new OA\AdditionalProperties(ref: '#/components/schemas/ImageData')
            )
        ),
        new OA\Property(
            property: 'files',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'string')
        ),
    ]
)]
class Block {}
