<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => 'gd',

    /*
    |--------------------------------------------------------------------------
    | Image Cache
    |--------------------------------------------------------------------------
    |
    | Here you may configure the image cache settings. When a URL is generated
    | for an image, a cached version is used if it exists, otherwise the image
    | is processed and the result is cached.
    |
    */

    'cache' => [
        'lifetime' => 43200, // 12 hours
        'prefix' => 'img',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Quality
    |--------------------------------------------------------------------------
    |
    | Here you may configure the default quality for image compression.
    | The quality setting affects the file size and visual quality of the
    | processed images.
    |
    */

    'quality' => 90,

    /*
    |--------------------------------------------------------------------------
    | Image Formats
    |--------------------------------------------------------------------------
    |
    | Here you may configure the supported image formats and their settings.
    |
    */

    'formats' => [
        'jpeg' => [
            'quality' => 90,
        ],
        'png' => [
            'compression' => 9,
        ],
        'webp' => [
            'quality' => 90,
        ],
    ],

];
