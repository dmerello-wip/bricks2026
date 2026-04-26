<?php

namespace App\View\Components\Twill;

use A17\Twill\View\Components\Blocks\TwillBlockComponent;
use Illuminate\Support\Str;

abstract class AppBlock extends TwillBlockComponent
{
    public static function getBlockIdentifier(): string
    {
        // removed app- prefix (slug from namespace)
        return Str::slug(Str::afterLast(static::class, '\\'));
    }
}
