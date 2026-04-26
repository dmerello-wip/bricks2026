<?php

namespace Database\Seeders;

use App\Models\Homepage;
use Illuminate\Database\Seeder;

class HomepageSeeder extends Seeder
{
    public function run(): void
    {
        $homepage = Homepage::updateOrCreate(
            ['id' => 1],
            ['published' => true]
        );

        foreach (config('translatable.locales', [config('app.locale')]) as $locale) {
            $homepage->translations()->updateOrCreate(
                ['locale' => $locale],
                ['title' => 'Home', 'active' => true]
            );
        }
    }
}
