<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasNesting;
use A17\Twill\Models\Behaviors\HasPosition;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Behaviors\Sortable;
use A17\Twill\Models\Model;
use App\Models\Concerns\HasSeoData;
use App\Observers\SitemapObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OpenApi\Attributes as OA;

/**
 * CMS Page model data as returned by $page->toArray().
 * Traits: HasTranslation (title, description), HasMedias, HasSeoData,
 *         HasSlug, HasPosition, HasNesting.
 */
#[OA\Schema(
    schema: 'PageModel',
    required: ['id', 'published'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'published', type: 'boolean'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'position', type: 'integer', nullable: true),
        new OA\Property(property: '_lft', type: 'integer'),
        new OA\Property(property: '_rgt', type: 'integer'),
        new OA\Property(property: 'parent_id', type: 'integer', nullable: true),
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
class Page extends Model implements Sortable
{
    use HasBlocks, HasFactory, HasFiles, HasMedias, HasNesting, HasPosition, HasRevisions, HasSlug, HasTranslation;
    use HasSeoData;

    protected static function booted(): void
    {
        static::observe(SitemapObserver::class);
    }

    protected $fillable = [
        'published',
        'title',
        'description',
        'position',
    ];

    public $translatedAttributes = [
        'title',
        'description',
    ];

    public $slugAttributes = [
        'title',
    ];
}
