<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Model;
use App\Models\Concerns\HasSeoData;
use App\Observers\SitemapObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OpenApi\Attributes as OA;

/**
 * Event model data as returned by $event->toArray().
 * Traits: HasTranslation (title, description), HasMedias, HasSlug, HasRevisions, HasSeoData.
 */
#[OA\Schema(
    schema: 'EventModel',
    required: ['id', 'published'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'published', type: 'boolean'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'data', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'luogo',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'latlng', type: 'string', nullable: true),
                new OA\Property(property: 'address', type: 'string', nullable: true),
            ]
        ),
        new OA\Property(property: 'luogo_lat', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'luogo_lng', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'deleted_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'medias',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TwillMedia')
        ),
    ]
)]
class Event extends Model
{
    use HasBlocks, HasFactory, HasMedias, HasRevisions, HasSeoData, HasSlug, HasTranslation;

    protected static function booted(): void
    {
        static::observe(SitemapObserver::class);
    }

    protected $fillable = [
        'published',
        'title',
        'description',
        'data',
        'luogo',
        'luogo_lat',
        'luogo_lng',
    ];

    public $translatedAttributes = [
        'title',
        'description',
    ];

    public $slugAttributes = [
        'title',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'data' => 'datetime',
            'luogo_lat' => 'float',
            'luogo_lng' => 'float',
        ];
    }

    /**
     * Twill's Map field persists its payload as a JSON string in the `luogo`
     * column. Decode it on read so the admin Map field and the frontend both
     * receive a structured object; writes pass through untouched (Twill
     * already submits a JSON string).
     *
     * @return Attribute<array{latlng?: string, address?: string}|null, never>
     */
    protected function luogo(): Attribute
    {
        return Attribute::get(
            fn (?string $value): ?array => $value ? json_decode($value, true) : null
        );
    }
}
