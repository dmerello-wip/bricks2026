<?php

use App\Models\Homepage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('returns a successful response', function () {
    Homepage::create(['published' => true]);

    $response = $this->get(route('home'));

    $response->assertOk();
});
