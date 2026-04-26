<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $types = [
        'app-hero' => 'hero',
        'app-paragraph' => 'paragraph',
        'app-cardslist' => 'cardslist',
        'app-editorialcard' => 'editorialcard',
    ];

    public function up(): void
    {
        foreach ($this->types as $old => $new) {
            DB::table('twill_blocks')
                ->where('type', $old)
                ->update(['type' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->types as $old => $new) {
            DB::table('twill_blocks')
                ->where('type', $new)
                ->update(['type' => $old]);
        }
    }
};
