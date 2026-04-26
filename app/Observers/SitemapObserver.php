<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SitemapObserver
{
    public function saved(Model $model): void
    {
        Cache::forget('sitemap.xml');
    }

    public function deleted(Model $model): void
    {
        Cache::forget('sitemap.xml');
    }
}
