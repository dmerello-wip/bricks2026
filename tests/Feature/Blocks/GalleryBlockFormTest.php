<?php

use A17\Twill\Services\Forms\Fields\Select;
use App\View\Components\Twill\Blocks\Gallery;

test('the gallery block form exposes a layout select with both carousel layouts', function () {
    $selects = collect(iterator_to_array((new Gallery)->getForm()))
        ->filter(fn ($field) => $field instanceof Select)
        ->map(fn (Select $field) => $field->render()->render())
        ->implode('');

    expect($selects)
        ->toContain('name="layout"')
        ->toContain('label="Layout"')
        ->toContain('peek')
        ->toContain('centered');
});
