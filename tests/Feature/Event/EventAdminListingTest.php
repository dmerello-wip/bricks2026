<?php

use A17\Twill\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Helper: create and authenticate a Twill admin user.
 */
function actingAsTwillUser(): User
{
    $user = new User;
    $user->forceFill([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'SUPERADMIN',
        'published' => true,
    ])->save();

    test()->actingAs($user, 'twill_users');

    return $user;
}

it('renders the events listing when an event stores Map extended data as JSON', function () {
    actingAsTwillUser();

    Event::factory()->create([
        'luogo' => json_encode([
            'latlng' => '52.908902047770255|27.18017578125',
            'address' => 'Piazza del Duomo, Milano',
            'boundingBox' => [],
            'types' => [],
        ]),
    ]);

    $this->get('/admin/events')->assertOk();
});

it('renders the events listing when luogo holds a plain (non-JSON) string', function () {
    actingAsTwillUser();

    Event::factory()->create(['luogo' => 'Via Roma 1, Milano']);

    $this->get('/admin/events')->assertOk();
});
