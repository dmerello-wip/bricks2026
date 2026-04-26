<?php

namespace App\Twill\Concerns;

use App\Services\TwillBlockService;
use Illuminate\View\View;

trait HasBlockPreview
{
    public function preview(int $id): View
    {
        $this->setPreviewView('admin.module-preview');

        return parent::preview($id);
    }

    protected function previewData($item): array
    {
        $rootBlocks = $item->blocks
            ->filter(fn ($block) => \is_null($block->parent_id))
            ->values();

        return [
            'blocks' => app(TwillBlockService::class)->formatBlocks($rootBlocks),
        ];
    }
}
