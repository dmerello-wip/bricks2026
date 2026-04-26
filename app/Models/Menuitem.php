<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Behaviors\HasNesting;
use A17\Twill\Models\Behaviors\HasPosition;
use A17\Twill\Models\Behaviors\HasRelated;
use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Behaviors\Sortable;
use A17\Twill\Models\Model;
use A17\Twill\Models\RelatedItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menuitem extends Model implements Sortable
{
    use HasFiles, HasNesting, HasPosition, HasRelated, HasTranslation;

    protected $fillable = [
        'published',
        'title',
        'description',
        'position',
        'menu_id',
        'parent_id',
        'type',
        'external_url',
        'target',
    ];

    public $translatedAttributes = [
        'title',
        'description',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function getDepthAttribute(): int
    {
        return $this->ancestors()->count();
    }

    /**
     * Eager-loadable relation to the linked content (Page or Category) via twill_related.
     */
    public function relatedContent(): HasMany
    {
        return $this->hasMany(RelatedItem::class, 'subject_id')
            ->whereIn('subject_type', [self::class, 'menuitems'])
            ->where('browser_name', 'related_content');
    }
}
