<?php

namespace App\Twill\Fields;

use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Browser;
use A17\Twill\Services\Forms\Fields\Files;

class BlockFields
{
    public static function textAlignment(): Select
    {
        return Select::make()
            ->name('text_alignment')
            ->label('Text Alignment')
            ->options([
                ['value' => 'text-left', 'label' => 'Left'],
                ['value' => 'text-center', 'label' => 'Center'],
                ['value' => 'text-right', 'label' => 'Right'],
            ])
            ->default('text-left');
    }

    public static function textColor(): Select
    {
        return Select::make()
            ->name('text_color')
            ->label('Text Color')
            ->options([
                ['value' => 'block-text-dark', 'label' => 'Dark'],
                ['value' => 'block-text-light', 'label' => 'Light'],
            ])
            ->default('block-text-dark');
    }

    public static function inputWithSeoTag(string $name, string $label, bool $required = false, bool $translatable = true): array
    {
        return [
            Columns::make()
                ->left([
                    Input::make()
                        ->name($name)
                        ->label($label)
                        ->required($required)
                        ->translatable($translatable),
                ])
                ->right([
                    Select::make()
                        ->name($name . '_seo')
                        ->label($label . ' HTML Tag')
                        ->placeholder('Select a tag')
                        ->default('div')
                        ->options([
                            ['value' => 'div', 'label' => 'div'],
                            ['value' => 'h1', 'label' => 'h1'],
                            ['value' => 'h2', 'label' => 'h2'],
                            ['value' => 'h3', 'label' => 'h3'],
                            ['value' => 'h4', 'label' => 'h4'],
                        ]),
                ]),
        ];
    }

    // TODO: Why not to make a block and use it in Inline Repeater as ->blocks(['cta_block']) ?
    public static function ctaFields(): array
    {
        return [
            Columns::make()
                ->left([
                    Input::make()->name('cta_label')->label('Label')->translatable(),
                ])
                ->right([
                    Select::make()->name('cta_style')->label('Style')->default('primary')
                        ->options([
                            ['value' => 'primary', 'label' => 'Primary'],
                            ['value' => 'secondary', 'label' => 'Secondary'],
                        ]),
                ]),

            Columns::make()
                ->left([
                    Select::make()->name('cta_type')->label('Type')->default('external')
                        ->options([
                            ['value' => 'external', 'label' => 'External'],
                            ['value' => 'internal', 'label' => 'Internal'],
                            ['value' => 'download', 'label' => 'Download'],
                        ]),
                ])
                ->right([
                    Input::make()
                        ->name('cta_external_link')
                        ->label('Link')
                        ->translatable()
                        ->connectedTo('cta_type', 'external'),

                    Browser::make()
                        ->name('pages')
                        ->modules([['label' => 'Pages', 'name' => 'pages']])
                        ->label('Page')
                        ->max(1)
                        ->connectedTo('cta_type', 'internal'),

                    Files::make()
                        ->name('cta_file')
                        ->label('File')
                        ->max(1)
                        ->connectedTo('cta_type', 'download'),
                ]),

            Checkbox::make()->name('cta_target_blank')->label('Open in new window'),
        ];
    }
}
