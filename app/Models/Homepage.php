<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Model;
use App\Models\Concerns\HasSeoData;
use App\Observers\SitemapObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OpenApi\Attributes as OA;

/**
 * Homepage model data as returned by $homepage->toArray().
 * Traits: HasTranslation (title), HasMedias, HasSeoData.
 */
#[OA\Schema(
    schema: 'HomepageModel',
    required: ['id', 'published'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'published', type: 'boolean'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
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
class Homepage extends Model
{
    use HasBlocks, HasFactory, HasMedias, HasRevisions, HasTranslation;
    use HasSeoData;

    protected static function booted(): void
    {
        static::observe(SitemapObserver::class);
    }

    protected $fillable = [
        'published',
        'title',
    ];

    public $translatedAttributes = [
        'title',
    ];
}
