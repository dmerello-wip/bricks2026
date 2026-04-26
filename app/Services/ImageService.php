<?php

namespace App\Services;

class ImageService
{
    /**
     * Build image data with Glide URL parameters
     *
     * @param object $imageSource Object with image() and imageAltText() methods (Block or Model)
     * @param mixed $media Media object with pivot data
     * @param string $role Image role
     * @param string $crop Crop name
     * @param string|null $configPath Config path for crop settings (e.g., 'twill.block_editor.crops' or 'twill.settings.crops')
     * @return array
     */
    public function buildImageData(
        object $imageSource,
        $media,
        string $role,
        string $crop,
        ?string $configPath = 'twill.block_editor.crops'
    ): array {
        $minValues = config("$configPath.$role.$crop.0.minValues");

        // Get original crop dimensions from pivot table
        $originalCropWidth = $media->pivot->crop_w;
        $originalCropHeight = $media->pivot->crop_h;
        $originalCropX = $media->pivot->crop_x;
        $originalCropY = $media->pivot->crop_y;

        $dimensions = $this->calculateImageDimensions(
            $originalCropWidth,
            $originalCropHeight,
            $minValues
        );

        $srcParams = $this->buildGlideParams(
            $dimensions['width'],
            $dimensions['height'],
            $originalCropX,
            $originalCropY,
            $originalCropWidth,
            $originalCropHeight
        );

        return [
            'src' => $imageSource->image($role, $crop, $srcParams),
            'width' => $dimensions['width'] ?? $originalCropWidth,
            'height' => $dimensions['height'] ?? $originalCropHeight,
            'alt' => $imageSource->imageAltText($role),
        ];
    }

    /**
     * Calculate output image dimensions based on crop ratio and min values
     *
     * @param int|null $originalCropWidth
     * @param int|null $originalCropHeight
     * @param array|null $minValues
     * @return array
     */
    public function calculateImageDimensions(?int $originalCropWidth, ?int $originalCropHeight, ?array $minValues): array
    {
        $outputWidth = null;
        $outputHeight = null;

        if ($originalCropWidth && $originalCropHeight) {
            $cropRatio = $originalCropHeight / $originalCropWidth;

            // Define proportions based on minValues configuration
            if (isset($minValues['width'])) {
                $outputWidth = $minValues['width'];
                $outputHeight = (int) ($outputWidth * $cropRatio);
            } elseif (isset($minValues['height'])) {
                $outputHeight = $minValues['height'];
                $outputWidth = (int) ($outputHeight / $cropRatio);
            } else {
                // If no minValues are set, use original crop dimensions as output
                $outputWidth = $originalCropWidth;
                $outputHeight = $originalCropHeight;
            }
        }

        return [
            'width' => $outputWidth,
            'height' => $outputHeight,
        ];
    }

    /**
     * Build Glide URL parameters for image manipulation
     *
     * @param int|null $outputWidth
     * @param int|null $outputHeight
     * @param int|null $originalCropX
     * @param int|null $originalCropY
     * @param int|null $originalCropWidth
     * @param int|null $originalCropHeight
     * @return array
     */
    public function buildGlideParams(
        ?int $outputWidth,
        ?int $outputHeight,
        ?int $originalCropX,
        ?int $originalCropY,
        ?int $originalCropWidth,
        ?int $originalCropHeight
    ): array {
        $srcParams = [];

        if ($outputWidth) {
            $srcParams['w'] = $outputWidth;
        }

        if ($outputHeight) {
            $srcParams['h'] = $outputHeight;
        }

        // Build crop parameter if all crop coordinates are available
        if ($originalCropX !== null && $originalCropY !== null && $originalCropWidth && $originalCropHeight) {
            $srcParams['crop'] = "$originalCropX,$originalCropY,$originalCropWidth,$originalCropHeight";
        }

        return $srcParams;
    }

    /**
     * Build image data from media collection for a given role and crop
     * Useful for standard form builder images
     *
     * @param object $model Model with image() and imageAltText() methods
     * @param string $role Image role
     * @param string $crop Crop name
     * @param string|null $configPath Config path for crop settings
     * @return array|null
     */
    public function getImageForRole(
        object $model,
        string $role,
        string $crop = 'default',
        ?string $configPath = 'twill.settings.crops'
    ): ?array {
        // Check if model has medias relationship
        if (!method_exists($model, 'medias')) {
            return null;
        }

        // Find media for the specified role
        $media = $model->medias->first(function ($media) use ($role) {
            return $media->pivot->role === $role;
        });

        if (!$media) {
            return null;
        }

        return $this->buildImageData($model, $media, $role, $crop, $configPath);
    }

    /**
     * Build multiple images data from media collection grouped by role and crop
     * Useful for models with multiple images
     *
     * @param object $model Model with medias relationship
     * @param string|null $configPath Config path for crop settings
     * @return array
     */
    public function getAllImages(
        object $model,
        ?string $configPath = 'twill.settings.crops'
    ): array {
        $images = [];

        if (!method_exists($model, 'medias')) {
            return $images;
        }

        foreach ($model->medias as $media) {
            $role = $media->pivot->role;
            $crop = $media->pivot->crop;

            $imageData = $this->buildImageData($model, $media, $role, $crop, $configPath);
            $images[$role][$crop] = $imageData;
        }

        return $images;
    }
}
