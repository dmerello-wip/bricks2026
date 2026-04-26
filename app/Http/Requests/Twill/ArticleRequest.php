<?php

namespace App\Http\Requests\Twill;

use A17\Twill\Http\Requests\Admin\Request;

class ArticleRequest extends Request
{
    public function rulesForCreate(): array
    {
        if (! $this->has('cmsSaveType')) {
            return [];
        }

        return [
            'browsers.categories' => ['required', 'array', 'min:1'],
        ];
    }

    public function rulesForUpdate(): array
    {
        return [
            'browsers.categories' => ['required', 'array', 'min:1'],
        ];
    }
}
