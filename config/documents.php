<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the storage paths for different types of documents.
    |
    */

    'storage' => [
        'documents' => 'documents',
        'signatures' => 'signatures',
        'archives' => 'archives',
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the upload settings for documents.
    |
    */

    'upload' => [
        'max_size' => env('MAX_DOCUMENT_SIZE', 10) * 1024, // KB
        'allowed_types' => ['pdf', 'png', 'jpg', 'jpeg'],
        'signature_max_size' => env('MAX_SIGNATURE_SIZE', 2) * 1024, // KB
        'signature_allowed_types' => ['png', 'jpg', 'jpeg'],
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Signing Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the PDF signing settings.
    |
    */

    'signing' => [
        'default_position' => [
            'x' => 100,
            'y' => 100,
            'width' => 150,
            'height' => 75,
            'page' => -1, // -1 = dernière page
        ],
        'opacity' => 0.8,
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Types
    |--------------------------------------------------------------------------
    |
    | Here you may configure the available document types.
    |
    */

    'types' => [
        'contrat' => 'Contrat',
        'facture' => 'Facture',
        'devis' => 'Devis',
        'bon_commande' => 'Bon de commande',
        'rapport' => 'Rapport',
        'autre' => 'Autre',
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Status
    |--------------------------------------------------------------------------
    |
    | Here you may configure the available document statuses.
    |
    */

    'statuses' => [
        'pending' => 'En attente',
        'in_progress' => 'En cours',
        'signed' => 'Signé',
        'refused' => 'Refusé',
    ],

];
