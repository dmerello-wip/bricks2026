<?php

use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function validSubscriptionPayload(array $overrides = []): array
{
    return array_merge([
        'band' => 'Bricks',
        'nr_componenti' => 4,
        'eta_media' => '20',
        'citta' => 'Milano',
        'genere' => 'Indie Rock',
        'durata' => 30,
        'referente' => 'Mario Rossi',
        'telefono' => '+39 333 1234567',
        'email' => 'mario@example.com',
        'video_link' => 'https://youtu.be/dQw4w9WgXcQ',
        'privacy' => '1',
        'evento' => 'bricks-music-festival-2026',
    ], $overrides);
}

it('stores a subscription with valid payload (link only)', function () {
    $response = $this->post('/subscriptions', validSubscriptionPayload());

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    expect(Subscription::count())->toBe(1);

    $subscription = Subscription::first();
    expect($subscription->band)->toBe('Bricks');
    expect($subscription->evento)->toBe('bricks-music-festival-2026');
    expect($subscription->video_link)->toBe('https://youtu.be/dQw4w9WgXcQ');
    expect($subscription->video_file_path)->toBeNull();
    expect($subscription->privacy)->toBeTrue();
    expect($subscription->data_iscrizione)->not->toBeNull();
});

it('stores a subscription with an uploaded video file', function () {
    Storage::fake('public');

    $payload = validSubscriptionPayload([
        'video_link' => null,
        'video_file_path' => UploadedFile::fake()->create('demo.mp4', 1024, 'video/mp4'),
    ]);

    $response = $this->post('/subscriptions', $payload);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $subscription = Subscription::first();
    expect($subscription)->not->toBeNull();
    expect($subscription->video_file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($subscription->video_file_path);
});

it('rejects an empty payload with 422-equivalent validation errors', function () {
    $response = $this->from('/it/welcome')->post('/subscriptions', []);

    $response->assertSessionHasErrors([
        'band',
        'nr_componenti',
        'eta_media',
        'citta',
        'genere',
        'durata',
        'referente',
        'telefono',
        'email',
        'privacy',
        'evento',
    ]);
});

it('rejects an underage average age', function () {
    $response = $this->from('/it/welcome')->post(
        '/subscriptions',
        validSubscriptionPayload(['eta_media' => '13'])
    );

    $response->assertSessionHasErrors(['eta_media']);
});

it('rejects an over-25 average age', function () {
    $response = $this->from('/it/welcome')->post(
        '/subscriptions',
        validSubscriptionPayload(['eta_media' => '26'])
    );

    $response->assertSessionHasErrors(['eta_media']);
});

it('rejects fewer than 2 band members', function () {
    $response = $this->from('/it/welcome')->post(
        '/subscriptions',
        validSubscriptionPayload(['nr_componenti' => 1])
    );

    $response->assertSessionHasErrors(['nr_componenti']);
});

it('requires either video_file_path or video_link', function () {
    $response = $this->from('/it/welcome')->post(
        '/subscriptions',
        validSubscriptionPayload(['video_link' => null])
    );

    $response->assertSessionHasErrors(['video_file_path', 'video_link']);
});

it('rejects a missing privacy consent', function () {
    $response = $this->from('/it/welcome')->post(
        '/subscriptions',
        validSubscriptionPayload(['privacy' => '0'])
    );

    $response->assertSessionHasErrors(['privacy']);
});
