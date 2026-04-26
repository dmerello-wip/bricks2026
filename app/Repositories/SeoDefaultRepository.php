<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\Behaviors\HandleTranslations;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\SeoDefault;

class SeoDefaultRepository extends ModuleRepository
{
    use HandleMedias, HandleRevisions, HandleTranslations;

    public function __construct(SeoDefault $model)
    {
        $this->model = $model;
    }
}
