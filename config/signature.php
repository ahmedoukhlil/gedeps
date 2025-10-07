<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration de la signature PDF
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient la configuration pour le positionnement automatique
    | des signatures sur les documents PDF.
    |
    */

    // Termes à rechercher pour positionner la signature
    'search_terms' => [
        'Directeur Général',
        'Directeur General',
        'DIRECTEUR GÉNÉRAL',
        'DIRECTEUR GENERAL',
        'Directeur',
        'DG',
        'Directeur Général:',
        'Directeur General:',
        'Signature:',
        'Signé par:',
    ],

    // Position par défaut si aucun terme n'est trouvé
    'default_position' => [
        'x_percentage' => 0.8,  // 80% de la largeur de la page (aligné à droite)
        'y_percentage' => 0.9,  // 90% de la hauteur de la page (en bas)
    ],

    // Taille de la signature
    'size' => [
        'png' => [
            'width' => 60,
            'height' => 30,
        ],
        'live' => [
            'width' => 70,
            'height' => 35,
        ],
    ],

    // Espacement
    'spacing' => [
        'margin' => 20,           // Marge depuis les bords
        'below_text' => 10,       // Espace sous le texte trouvé
        'text_height' => 15,      // Hauteur estimée du texte
    ],

    // Configuration du texte d'information
    'info_text' => [
        'font_size' => 7,
        'line_height' => 4,
        'max_comment_length' => 25,
    ],
];