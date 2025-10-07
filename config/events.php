<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document Events Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the document-related events and their settings.
    |
    */

    'document' => [
        'upload' => [
            'enabled' => true,
            'notify_admins' => true,
        ],
        'sign' => [
            'enabled' => true,
            'notify_uploader' => true,
        ],
        'refuse' => [
            'enabled' => true,
            'notify_uploader' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure the notification settings for document events.
    |
    */

    'notifications' => [
        'email' => [
            'enabled' => true,
            'queue' => 'default',
        ],
        'database' => [
            'enabled' => true,
        ],
    ],

];