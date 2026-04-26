<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoDefault extends Model
{
    use HasFactory, HasMedias, HasRevisions, HasTranslation;

    protected $fillable = [
        'published',
        'title',
    ];

    public $translatedAttributes = [
        'default_title',
        'default_description',
        'default_og_title',
        'default_og_description',
    ];

    public $mediasParams = [
        'default_og_image' => [
            'default' => [
                ['name' => 'default', 'ratio' => 1200 / 630, 'minWidth' => 1200, 'minHeight' => 630],
            ],
        ],
    ];
}
