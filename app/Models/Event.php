<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Model;
use App\Models\Concerns\HasSeoData;
use App\Observers\SitemapObserver;
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
        new OA\Property(property: 'luogo', type: 'string', nullable: true),
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
    use HasFactory, HasMedias, HasRevisions, HasSeoData, HasSlug, HasTranslation;

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
}
