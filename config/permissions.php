<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may configure the document-related permissions.
    |
    */

    'documents' => [
        'upload' => 'documents.upload',
        'view' => 'documents.view',
        'view_own' => 'documents.view-own',
        'approve' => 'documents.approve',
        'sign' => 'documents.sign',
        'refuse' => 'documents.refuse',
        'download' => 'documents.download',
        'history' => 'documents.history',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may configure the permissions for each role.
    |
    */

    'roles' => [
        'agent' => [
            'documents.upload',
            'documents.view-own',
            'documents.download',
        ],
        'dg' => [
            'documents.upload',
            'documents.view',
            'documents.view-own',
            'documents.approve',
            'documents.sign',
            'documents.refuse',
            'documents.download',
            'documents.history',
        ],
        'daf' => [
            'documents.upload',
            'documents.view',
            'documents.view-own',
            'documents.approve',
            'documents.sign',
            'documents.refuse',
            'documents.download',
            'documents.history',
        ],
    ],

];
