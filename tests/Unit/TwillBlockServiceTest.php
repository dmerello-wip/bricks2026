<?php

use App\Services\TwillBlockService;

pest()->extend(Tests\TestCase::class);

function invokeLocalizedContent(array $content): array
{
    $service = app(TwillBlockService::class);
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('localizedContent');
    $method->setAccessible(true);

    return $method->invoke($service, $content);
}

it('extracts the current locale value from a translatable field', function () {
    app()->setLocale('it');

    $result = invokeLocalizedContent([
        'title' => ['it' => 'Ciao', 'en' => 'Hello'],
    ]);

    expect($result['title'])->toBe('Ciao');
});

it('returns null when the current locale value is missing from a legacy locale map', function () {
    app()->setLocale('it');

    $result = invokeLocalizedContent([
        'title' => ['en' => 'Legacy only'],
    ]);

    expect($result['title'])->toBeNull();
});

it('does not flatten plain associative arrays that look nothing like a locale map', function () {
    app()->setLocale('it');

    $payload = ['width' => 100, 'height' => 200];
    $result = invokeLocalizedContent(['size' => $payload]);

    expect($result['size'])->toBe($payload);
});

it('does not flatten sequential arrays', function () {
    app()->setLocale('it');

    $payload = ['it', 'en'];
    $result = invokeLocalizedContent(['locales' => $payload]);

    expect($result['locales'])->toBe($payload);
});
