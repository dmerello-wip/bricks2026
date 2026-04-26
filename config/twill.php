<?php

return [
    'block_editor' => [
        'files' => [
            'video_file',
            'download_url',
            'cta_file',
        ],
        'crops' => [
            'hero_image_desktop' => [
                'default' => [
                    [
                        'name' => 'default',
                        'ratio' => 0,
                        'minValues' => [
                            'width' => 1920,
                            'height' => 400,
                        ],
                    ],
                ],
            ],
            'hero_image_mobile' => [
                'default' => [
                    [
                        'name' => 'default',
                        'ratio' => 0,
                        'minValues' => [
                            'width' => 375,
                        ],
                    ],
                ],
            ],
            'card_image' => [
                'default' => [
                    [
                        'name' => 'default',
                        'ratio' => 16 / 9,
                        'minValues' => [
                            'width' => 720,
                        ],
                    ],
                ],
            ],
            'image' => [
                'default' => [
                    [
                        'name' => 'default',
                        'ratio' => 0,
                    ],
                ],
            ],
            'gallery_image' => [
                'default' => [
                    [
                        'name' => 'default',
                        'ratio' => 0,
                    ],
                ],
            ],
            'abstract_image' => [
                'default' => [
                    [
                        'name' => 'default',
                        'ratio' => 0,
                        'minValues' => [
                            'width' => 720,
                        ],
                    ],
                ],
            ],
        ],
    ],
    'media_library' => [
        'disk' => 'twill_media_library',
        'endpoint_type' => env('MEDIA_LIBRARY_ENDPOINT_TYPE', 'local'),
        'cascade_delete' => env('MEDIA_LIBRARY_CASCADE_DELETE', true),
        'local_path' => env('MEDIA_LIBRARY_LOCAL_PATH', 'uploads'),
        'image_service' => env('MEDIA_LIBRARY_IMAGE_SERVICE', 'A17\Twill\Services\MediaLibrary\Glide'),
        'acl' => env('MEDIA_LIBRARY_ACL', 'private'),
        'filesize_limit' => env('MEDIA_LIBRARY_FILESIZE_LIMIT', 50),
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'],
        'init_alt_text_from_filename' => true,
    ],
    'glide' => [
        'base_path' => env('IMAGE_CACHE_PATH', 'storage/img/crops'),
        'original_media_for_extensions' => ['gif', 'svg'],
        'default_params' => [
            'fm' => env('MEDIA_LIBRARY_DEFAULT_FORMAT', 'jpg'),
            'w' => 1920,
            'q' => 90,
        ],
    ],
];
