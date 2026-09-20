<?php

use App\Models\Homepage;
use Illuminate\Support\Facades\Http;

it('dispatches to the remote SSR server without a local bundle when ensure_bundle_exists is disabled', function () {
    Homepage::create(['published' => true]);

    config([
        'inertia.ssr.enabled' => true,
        'inertia.ssr.ensure_bundle_exists' => false,
        'inertia.ssr.url' => 'http://ssr:13714',
        'inertia.ssr.bundle' => base_path('bootstrap/ssr/does-not-exist.js'),
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'http://ssr:13714/render' => Http::response([
            'head' => ['<title inertia>SSR Title</title>'],
            'body' => '<div id="app" data-page="{}"><h1>Rendered by SSR</h1></div>',
        ]),
    ]);

    $response = $this->get('/it/')
        ->assertSuccessful()
        ->assertSee('Rendered by SSR', false)
        ->assertSee('<title inertia>SSR Title</title>', false);

    expect(substr_count($response->getContent(), '<title'))->toBe(1);

    Http::assertSent(fn ($request) => $request->url() === 'http://ssr:13714/render');
});

it('reads the ensure_bundle_exists flag from the environment with a safe default', function () {
    expect(config('inertia.ssr.ensure_bundle_exists'))->toBeTrue();
});
