<?php

namespace App\Services;

use A17\Twill\Models\Block;
use A17\Twill\Services\FileLibrary\FileService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TwillBlockService
{
    public function __construct(
        private ImageService $imageService
    ) {}

    public function formatBlocks(Collection $blocks): array
    {
        return $blocks->map(fn ($block) => $this->formatBlock($block))->toArray();
    }

    /**
     * Format blocks and its children recursively
     * to get clean data for frontend consumption
     */
    public function formatBlock(Block $block): array
    {
        $blockData = $block->toArray();
        $blockData['images'] = [];

        // Map Ctas Inline Repeaters
        if ($block->type === 'dynamic-repeater-ctas') {
            $blockData['ctas'] = $block->children->map(fn ($child) => $child->content)->toArray();
        }

        // Map Images for Roles and Crops
        foreach ($block->medias as $media) {
            $role = $media->pivot->role;
            $crop = $media->pivot->crop;

            $imageData = $this->imageService->buildImageData($block, $media, $role, $crop);
            $blockData['images'][$role][$crop] = $imageData;
        }

        // Recursively format children blocks
        if ($block->children->isNotEmpty()) {
            $blockData['children'] = $block->children->map(function ($child) {
                if ($child->type === 'dynamic-repeater-ctas') {
                    return $this->formatCtaBlock($child);
                }

                return $this->formatBlock($child);
            })->toArray();
        } else {
            $blockData['children'] = [];
        }

        $files = [];
        $fileDisk = Storage::disk(config('twill.file_library.disk', 'libraries'));
        foreach ($block->files as $file) {
            $role = $file->pivot->role;
            $files[$role] = $fileDisk->url($file->uuid);
        }

        $data = [
            'id' => $block->id,
            'parent_id' => $block->parent_id,
            'type' => $block->type,
            'content' => $this->localizedContent($block->content ?? []),
            'images' => $blockData['images'],
            'files' => $files,
            'children' => $blockData['children'],
        ];

        return $data;
    }

    private function localizedContent(array $content): array
    {
        $locale = app()->getLocale();

        foreach ($content as $key => $value) {
            if (is_array($value) && $this->isLocaleMap($value)) {
                $content[$key] = $value[$locale] ?? null;
            }
        }

        return $content;
    }

    private function isLocaleMap(array $value): bool
    {
        if ($value === [] || array_is_list($value)) {
            return false;
        }

        foreach (array_keys($value) as $key) {
            if (! is_string($key) || preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $key) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Format a CTA inline repeater block for the frontend
     */
    private function formatCtaBlock(Block $block): array
    {
        $ctaType = $block->content['cta_type'] ?? 'external';

        // Resolve internal page URL via browser relation
        $internalUrl = null;
        if ($ctaType === 'internal') {
            $page = $block->getRelated('pages')->first();
            if ($page) {
                $slug = $page->slugs()
                    ->where('active', true)
                    ->where('locale', app()->getLocale())
                    ->first()
                    ?->slug;

                $internalUrl = $slug ? '/'.app()->getLocale().'/'.$slug : null;
            }
        }

        // Resolve download file URL via file library
        $downloadUrl = null;
        $downloadFilename = null;
        if ($ctaType === 'download') {
            $file = $block->files->first(fn ($f) => $f->pivot->role === 'cta_file');
            if ($file) {
                $downloadUrl = FileService::getUrl($file->uuid);
                $downloadFilename = $file->filename;
            }
        }

        return [
            'id' => $block->id,
            'type' => $block->type,
            'content' => [
                'cta_label' => $block->content['cta_label'][app()->getLocale()] ?? null,
                'cta_style' => $block->content['cta_style'] ?? 'primary',
                'cta_type' => $ctaType,
                'cta_link' => $ctaType === 'external' ? ($block->content['cta_external_link'][app()->getLocale()] ?? null) : $internalUrl,
                'cta_target_blank' => $block->content['cta_target_blank'] ?? false,
                'cta_dl_link' => $downloadUrl,
                'cta_dl_filename' => $downloadFilename,
            ],
        ];
    }
}
