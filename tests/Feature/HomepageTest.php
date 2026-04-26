<?php

use App\Models\Homepage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a successful response when homepage is published', function () {
    Homepage::create(['published' => true]);

    $this->get('/en/')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Homepage'));
});

it('returns 503 when homepage is not published', function () {
    Homepage::create(['published' => false]);

    $this->get('/en/')
        ->assertStatus(503);
});

it('returns 503 when no homepage record exists', function () {
    $this->get('/en/')
        ->assertStatus(503);
});

it('passes page and blocks props to inertia', function () {
    Homepage::create(['published' => true]);

    $this->get('/en/')
        ->assertInertia(fn ($page) => $page
            ->component('Homepage')
            ->has('page')
            ->has('blocks')
        );
});
