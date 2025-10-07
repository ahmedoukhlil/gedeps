<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration UX Moderne
    |--------------------------------------------------------------------------
    |
    | Configuration pour le système UX moderne de GEDEPS
    |
    */

    'assets' => [
        'css' => [
            'ux-modern' => 'css/ux-modern.css',
        ],
        'js' => [
            'pdf-overlay' => 'js/pdf-overlay-signature-module.js',
        ],
    ],

    'features' => [
        'animations' => env('UX_ANIMATIONS', true),
        'toast_notifications' => env('UX_TOAST_NOTIFICATIONS', true),
        'keyboard_shortcuts' => env('UX_KEYBOARD_SHORTCUTS', true),
        'drag_drop' => env('UX_DRAG_DROP', true),
    ],

    'performance' => [
        'lazy_loading' => env('UX_LAZY_LOADING', true),
        'preload_critical' => env('UX_PRELOAD_CRITICAL', true),
        'minify_assets' => env('UX_MINIFY_ASSETS', true),
    ],

    'themes' => [
        'default' => [
            'primary' => '#667eea',
            'secondary' => '#764ba2',
            'success' => '#28a745',
            'warning' => '#ffc107',
            'danger' => '#dc3545',
            'info' => '#17a2b8',
        ],
    ],
];
