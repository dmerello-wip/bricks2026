<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\Menu;

class MenuRepository extends ModuleRepository
{
    use HandleRevisions;

    public function __construct(Menu $model)
    {
        $this->model = $model;
    }
}
