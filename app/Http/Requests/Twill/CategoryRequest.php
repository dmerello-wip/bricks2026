<?php

namespace App\Http\Requests\Twill;

use A17\Twill\Http\Requests\Admin\Request;

class CategoryRequest extends Request
{
    public function rulesForCreate(): array
    {
        return [
            'title.*' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function rulesForUpdate(): array
    {
        return [
            'title.*' => ['nullable', 'string', 'max:200'],
        ];
    }
}
