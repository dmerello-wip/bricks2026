<?php

use A17\Twill\Models\Block;
use App\Models\Event;
use App\Models\Page;
use App\Services\TwillBlockService;
use Illuminate\Support\Facades\DB;

/**
 * Helper: create a hero block with a single CTA child linked to $related through the $browserName browser.
 */
function createHeroWithLinkedCta(string $ctaType, string $browserName, Event $related): Block
{
    $page = Page::create(['published' => true]);

    $hero = Block::create([
        'blockable_id' => $page->id,
        'blockable_type' => Page::class,
        'position' => 1,
        'content' => [],
        'type' => 'hero',
        'child_key' => null,
        'editor_name' => 'default',
    ]);

    $cta = Block::create([
        'blockable_id' => $page->id,
        'blockable_type' => Page::class,
        'parent_id' => $hero->id,
        'position' => 1,
        'content' => [
            'cta_label' => ['it' => 'Scopri'],
            'cta_type' => $ctaType,
            'cta_style' => 'primary',
        ],
        'type' => 'dynamic-repeater-ctas',
        'child_key' => 'ctas',
        'editor_name' => 'default',
    ]);

    DB::table('twill_related')->insert([
        'subject_id' => $cta->id,
        'subject_type' => $cta->getMorphClass(),
        'related_id' => $related->id,
        'related_type' => $related->getMorphClass(),
        'browser_name' => $browserName,
        'position' => 1,
    ]);

    return $hero->fresh();
}

test('a cta of type event resolves to the localized event url as an internal link', function () {
    app()->setLocale('it');

    $event = Event::factory()->create();
    DB::table('event_slugs')->insert([
        ['event_id' => $event->id, 'locale' => 'it', 'slug' => 'concerto', 'active' => true],
    ]);

    $hero = createHeroWithLinkedCta('event', 'events', $event);

    $cta = app(TwillBlockService::class)->formatBlock($hero)['children'][0]['content'];

    expect($cta['cta_link'])->toBe('/it/eventi/concerto')
        ->and($cta['cta_type'])->toBe('internal');
});

test('a cta of type event without an active slug renders no link', function () {
    app()->setLocale('it');

    $event = Event::factory()->create();

    $hero = createHeroWithLinkedCta('event', 'events', $event);

    $cta = app(TwillBlockService::class)->formatBlock($hero)['children'][0]['content'];

    expect($cta['cta_link'])->toBeNull();
});
