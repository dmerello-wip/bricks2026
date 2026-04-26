<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleTranslations;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\Article;
use App\Repositories\Concerns\HandleSeoData;

class ArticleRepository extends ModuleRepository
{
    use HandleBlocks, HandleMedias, HandleRevisions, HandleSlugs, HandleTranslations;
    use HandleSeoData;

    protected $relatedBrowsers = [
        'categories' => [
            'moduleName' => 'categories',
        ],
    ];

    public function __construct(Article $model)
    {
        $this->model = $model;
    }
}
