<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRelated;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Model;
use App\Models\Concerns\HasSeoData;
use App\Observers\SitemapObserver;
use OpenApi\Attributes as OA;

/**
 * Article model data as returned by $article->toArray().
 * Traits: HasTranslation (title, description), HasMedias, HasSlug, HasRelated, HasSeoData.
 */
#[OA\Schema(
    schema: 'ArticleModel',
    required: ['id', 'published'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'published', type: 'boolean'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'deleted_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'medias',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TwillMedia')
        ),
        new OA\Property(
            property: 'related',
            type: 'array',
            items: new OA\Items(type: 'object')
        ),
    ]
)]
class Article extends Model
{
    use HasBlocks, HasMedias, HasRelated, HasRevisions, HasSlug, HasTranslation;
    use HasSeoData;

    protected static function booted(): void
    {
        static::observe(SitemapObserver::class);
    }

    protected $fillable = [
        'published',
        'title',
        'description',
    ];

    public $translatedAttributes = [
        'title',
        'description',
    ];

    public $slugAttributes = [
        'title',
    ];
}
