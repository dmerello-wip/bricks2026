<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

/**
 * Processed image data returned by ImageService::buildImageData().
 */
#[OA\Schema(
    schema: 'ImageData',
    required: ['src', 'alt'],
    properties: [
        new OA\Property(property: 'src', type: 'string'),
        new OA\Property(property: 'width', type: 'integer', nullable: true),
        new OA\Property(property: 'height', type: 'integer', nullable: true),
        new OA\Property(property: 'alt', type: 'string'),
    ]
)]
class ImageData {}
