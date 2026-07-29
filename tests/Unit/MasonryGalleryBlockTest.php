<?php

use A17\Twill\Facades\TwillBlocks;
use A17\Twill\Services\Forms\InlineRepeater;
use App\View\Components\Twill\Blocks\MasonryGallery;

pest()->extend(Tests\TestCase::class);

it('exposes the identifier the React BlockRenderer is keyed on', function () {
    expect(MasonryGallery::getBlockIdentifier())->toBe('masonrygallery');
});

it('is discovered by Twill as a component block', function () {
    $block = TwillBlocks::getBlocks()
        ->first(fn ($block) => $block->name === 'masonrygallery');

    expect($block)->not->toBeNull()
        ->and($block->componentClass)->toBe('\\'.MasonryGallery::class);
});

it('names the items repeater so the React child type filter matches', function () {
    $repeater = collect(iterator_to_array((new MasonryGallery)->getForm()))
        ->first(fn ($field) => $field instanceof InlineRepeater);

    expect($repeater)->not->toBeNull()
        ->and($repeater->getRenderName())->toBe('dynamic-repeater-masonry_items');
});

it('declares a crop for the item image role', function () {
    expect(config('twill.block_editor.crops.masonry_image.default'))->toBeArray()->not->toBeEmpty();
});

it('is enabled in the Pages and Events block editors', function () {
    $sources = [
        app_path('Http/Controllers/Twill/PageController.php'),
        app_path('Http/Controllers/Twill/EventController.php'),
    ];

    foreach ($sources as $source) {
        expect(file_get_contents($source))->toContain("'masonrygallery'");
    }
});

it('is registered in the React BlockRenderer map', function () {
    $renderer = file_get_contents(resource_path('js/components/editorial/BlockRenderer.tsx'));

    expect($renderer)
        ->toContain('masonrygallery: MasonryGallery')
        ->toContain("import MasonryGallery from './MasonryGallery'");
});
