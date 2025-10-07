<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des notifications par email
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient la configuration pour les notifications par email
    | du système GEDEPS.
    |
    */

    'enabled' => env('MAIL_NOTIFICATIONS_ENABLED', true),

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@gedeps.com'),
        'name' => env('MAIL_FROM_NAME', 'GEDEPS - Système de Gestion Électronique'),
    ],

    'templates' => [
        'document_assigned' => 'emails.document-assigned',
        'document_signed' => 'emails.document-signed',
        'document_paraphed' => 'emails.document-paraphed',
    ],

    'queue' => [
        'enabled' => env('MAIL_QUEUE_ENABLED', false),
        'connection' => env('MAIL_QUEUE_CONNECTION', 'default'),
    ],

    'retry' => [
        'attempts' => env('MAIL_RETRY_ATTEMPTS', 3),
        'delay' => env('MAIL_RETRY_DELAY', 60), // en secondes
    ],
];
