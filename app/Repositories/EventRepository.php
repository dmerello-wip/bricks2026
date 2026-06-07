<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleTranslations;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\Event;
use App\Repositories\Concerns\HandleSeoData;

class EventRepository extends ModuleRepository
{
    use HandleBlocks, HandleMedias, HandleRevisions, HandleSlugs, HandleTranslations;
    use HandleSeoData;

    public function __construct(Event $model)
    {
        $this->model = $model;
    }
}
