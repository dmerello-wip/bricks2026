<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\Behaviors\HandleTranslations;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\Homepage;
use App\Repositories\Concerns\HandleSeoData;

class HomepageRepository extends ModuleRepository
{
    use HandleBlocks, HandleMedias, HandleRevisions, HandleSeoData, HandleTranslations;

    public function __construct(Homepage $model)
    {
        $this->model = $model;
    }
}
