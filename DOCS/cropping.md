# Image Cropping System

## Overview

On-demand image cropping and caching using Glide:

Flow:

- Glide URL is generated through Twill CMS configs and Twill admin crop tool data via [`ImageService`](app/Services/ImageService.php)
- Glide URL is handled by routing and image is generated
- Images are cached, supporting both local and S3 storage
- Works for both Twill Blocks and standard form images

## Architecture

This architecture allows the same image processing logic to be used for both block images and standard form builder images.

## Components

1. **Configuration** ([`config/twill.php`](config/twill.php))
   - Defines crop roles and their aspect ratios
   - Sets minimum dimensions for each crop
   - Configures Glide defaults
   
2. **Image Service** ([`ImageService`](app/Services/ImageService.php))
   - Calculates output dimensions based on crop configurations
   - Builds Glide URL parameters
   - Provides reusable methods for both blocks and standard form images
   - Handles image data formatting with dimensions and alt text
   - more on [`DOCS/image-service-usage.md`](DOCS/image-service-usage.md)

3. **Block Service** ([`TwillBlockService`](app/Services/TwillBlockService.php))
   - Block-specific formatting that uses ImageService
   - Formats block data for frontend consumption
   - Uses [`ImageService`](app/Services/ImageService.php) for image processing
   - Handles block-specific logic (CTAs, children, etc.)

4. **Route Handler** ([`routes/web.php`](routes/web.php:11-13))
   - Intercepts requests to `storage/img/crops/{path}`
   - Routes to [`ImageCropperController`](app/Http/Controllers/ImageCropperController.php)

5. **Image Processing** ([`ImageCropperController`](app/Http/Controllers/ImageCropperController.php))
   - Handles image retrieval from local or S3 storage
   - Applies Glide transformations
   - Manages caching strategy

## Caching Logic

### Cache Key Generation

Cache filenames are generated using MD5 hash of Glide parameters:

```php
$paramHash = md5(http_build_query($glideParams));
$cacheFilename = "{filename}_{hash}.{extension}";
```

**Location**: `storage/app/public/img/crops/{folder}/{cacheFilename}`

### Cache Flow

1. **Request arrives** at `/storage/img/crops/{path}?w=1920&h=400&crop=...`
2. **Check cache**: If file exists at hashed path, serve immediately
3. **Process image**: If not cached:
   - Retrieve source image (local or S3)
   - Apply Glide transformations
   - Save to cache directory with 0664 permissions
   - Clean up temporary files (S3 only)
4. **Serve with headers**:
   - `Cache-Control: max-age=31536000, public` (1 year)
   - `Expires: {1 year from now}`

### Cache Invalidation

- Cache is **permanent** until manually cleared
- Different parameters = different cache file
- No automatic cleanup mechanism

## Aspect Ratio & Dimension Calculation

### Configuration Structure

Crops are defined in [`config/twill.php`](config/twill.php:5-29):

```php
'crops' => [
    'hero_image_desktop' => [
        'default' => [
            [
                'name' => 'default',
                'ratio' => 0,  // Free aspect ratio
                'minValues' => [
                    'width' => 1920,
                    'height' => 400,
                ]
            ]
        ],
    ],
]
```

### Dimension Logic

The [`calculateImageDimensions()`](app/Services/ImageService.php:58) method in [`ImageService`](app/Services/ImageService.php) determines output dimensions:

1. **Calculate crop ratio** from original crop dimensions:
   ```php
   $cropRatio = $originalCropHeight / $originalCropWidth;
   ```

2. **Apply minValues constraints**:
   - **If `width` is set**: Use configured width, calculate height maintaining ratio
     ```php
     $outputWidth = $minValues['width'];
     $outputHeight = (int) ($outputWidth * $cropRatio);
     ```
   - **If only `height` is set**: Use configured height, calculate width maintaining ratio
     ```php
     $outputHeight = $minValues['height'];
     $outputWidth = (int) ($outputHeight / $cropRatio);
     ```
   - **If no minValues**: Use original crop dimensions as-is

3. **Preserve aspect ratio**: The crop ratio from Twill's editor is always maintained, ensuring no distortion

### Example

For `hero_image_desktop` with `minValues: {width: 1920, height: 400}`:

- Editor crop: 1600x800 (ratio = 0.5)
- Output: 1920x960 (maintains 0.5 ratio, respects min width)

For `hero_image_mobile` with `minValues: {width: 375}`:

- Editor crop: 800x1200 (ratio = 1.5)
- Output: 375x562 (maintains 1.5 ratio, respects min width)

## Glide Parameters

### Parameter Building

The [`buildGlideParams()`](app/Services/ImageService.php:95) method constructs URL parameters:

```php
[
    'w' => $outputWidth,           // Target width
    'h' => $outputHeight,          // Target height
    'crop' => 'x,y,width,height',  // Crop coordinates from Twill editor
    'fm' => 'jpg',                 // Format (from config)
    'q' => 90,                     // Quality (from config)
]
```

### Crop Coordinates

Coordinates come from Twill's media pivot table:
- `crop_x`, `crop_y`: Top-left corner of crop area
- `crop_w`, `crop_h`: Width and height of crop area

These define **which portion** of the original image to extract before resizing.

## Storage Backend Support

### Local Storage

- **Source path**: `{twill.glide.source}/{folder}/{filename}`
- **Default**: `storage/app/public/uploads/`
- **Validation**: Checks file existence with `file_exists()`

### S3 Storage

- **Source**: Downloads from S3 to temporary file
- **Temp location**: `sys_get_temp_dir()/s3/s3_img_{random}`
- **Cleanup**: Temporary file deleted after processing
- **Validation**: Checks existence with `Storage::disk('s3')->exists()`

### Configuration

Set in [`.env`](.env.example):
```env
MEDIA_LIBRARY_ENDPOINT_TYPE=local  # or 's3'
```

## Frontend Integration

### Block Data Structure

[`TwillBlockService::formatBlock()`](app/Services/TwillBlockService.php:23) outputs (using [`ImageService`](app/Services/ImageService.php) internally):

```php
[
    'images' => [
        'hero_image_desktop' => [
            'default' => [
                'src' => '/storage/img/crops/folder/image_hash.jpg?w=1920&h=960&crop=...',
                'width' => 1920,
                'height' => 960,
                'alt' => 'Alt text from Twill',
            ]
        ],
        'hero_image_mobile' => [
            'default' => [
                'src' => '/storage/img/crops/folder/image_hash.jpg?w=375&h=562&crop=...',
                'width' => 375,
                'height' => 562,
                'alt' => 'Alt text from Twill',
            ]
        ]
    ]
]
```

### Usage in Components

Access images by role and crop name:

```tsx
const desktopImage = block.images.hero_image_desktop?.default;
<img src={desktopImage.src} width={desktopImage.width} height={desktopImage.height} alt={desktopImage.alt} />
```

## Performance Considerations


### Potential Issues

- **Storage growth**: Cache never auto-purges (manual cleanup needed)
- **S3 latency**: First request downloads from S3 (subsequent requests cached)
- **Memory usage**: Large images processed in-memory by Glide

## Configuration Reference

### Key Settings in [`config/twill.php`](config/twill.php)

| Setting | Default | Description |
|---------|---------|-------------|
| `glide.base_path` | `storage/img/crops` | URL path for cropped images |
| `glide.default_params.fm` | `jpg` | Default output format |
| `glide.default_params.w` | `1920` | Default max width |
| `glide.default_params.q` | `90` | JPEG quality (1-100) |
| `media_library.endpoint_type` | `local` | Storage backend (`local` or `s3`) |
| `media_library.disk` | `twill_media_library` | Laravel disk for media |

### Environment Variables

```env
IMAGE_CACHE_PATH=storage/img/crops
MEDIA_LIBRARY_ENDPOINT_TYPE=local
MEDIA_LIBRARY_DEFAULT_FORMAT=jpg
```


### Clearing Cache

```bash
# Clear all cached crops
rm -rf storage/app/public/img/crops/*

# Clear specific folder
rm -rf storage/app/public/img/crops/{folder}/*
```
