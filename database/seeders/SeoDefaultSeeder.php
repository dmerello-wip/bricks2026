<?php

namespace Database\Seeders;

use App\Models\SeoDefault;
use Illuminate\Database\Seeder;

class SeoDefaultSeeder extends Seeder
{
    public function run(): void
    {
        if (SeoDefault::count() === 0) {
            SeoDefault::create(['published' => true, 'title' => 'SEO Defaults']);
        }
    }
}
