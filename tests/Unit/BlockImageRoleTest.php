<?php

use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\InlineRepeater;

pest()->extend(Tests\TestCase::class);

/**
 * Twill stores block media under a role named exactly after the Medias field.
 * The React component must read `block.images?.<role>`, so a rename on either
 * side silently drops the image — this test pins the two together.
 *
 * @return list<string>
 */
function declaredImageRoles(string $blockClass): array
{
    $roles = [];

    $collect = function (iterable $fields) use (&$collect, &$roles): void {
        foreach ($fields as $field) {
            if ($field instanceof Medias) {
                $roles[] = (new ReflectionClass($field))->getProperty('name')->getValue($field);

                continue;
            }

            if ($field instanceof InlineRepeater) {
                $collect((new ReflectionClass($field))->getProperty('fields')->getValue($field));

                continue;
            }

            if (method_exists($field, 'getLeftColumn')) {
                $collect([...$field->getLeftColumn(), ...$field->getRightColumn()]);
            }
        }
    };

    $collect(iterator_to_array((new $blockClass)->getForm()));

    return $roles;
}

dataset('imageBlocks', [
    'abstract' => ['AbstractBlock', 'Abstract.tsx'],
    'hero' => ['Hero', 'Hero.tsx'],
    'gallery' => ['Gallery', 'Gallery.tsx'],
    'matrix' => ['Matrix', 'Matrix.tsx'],
    'cards list' => ['CardsList', 'EditorialCard.tsx'],
    'masonry gallery' => ['MasonryGallery', 'MasonryGallery.tsx'],
]);

it('reads every declared image role in the matching React component', function (string $block, string $component) {
    $roles = declaredImageRoles('App\\View\\Components\\Twill\\Blocks\\'.$block);
    $source = file_get_contents(resource_path('js/components/editorial/'.$component));

    expect($roles)->not->toBeEmpty();

    foreach ($roles as $role) {
        expect($source)->toContain('images?.'.$role);
    }
})->with('imageBlocks');
