<?php

namespace App\Helpers;

class UXHelper
{
    /**
     * Génère le lien vers un asset UX
     */
    public static function asset($path)
    {
        $config = config('ux.assets');
        
        if (isset($config['css'][$path])) {
            return asset($config['css'][$path]);
        }
        
        if (isset($config['js'][$path])) {
            return asset($config['js'][$path]);
        }
        
        return asset($path);
    }

    /**
     * Vérifie si une fonctionnalité UX est activée
     */
    public static function isFeatureEnabled($feature)
    {
        return config("ux.features.{$feature}", false);
    }

    /**
     * Génère les classes CSS pour les animations
     */
    public static function animationClasses()
    {
        if (!self::isFeatureEnabled('animations')) {
            return '';
        }
        
        return 'animate-fadeInUp';
    }

    /**
     * Génère les attributs pour les raccourcis clavier
     */
    public static function keyboardShortcuts()
    {
        if (!self::isFeatureEnabled('keyboard_shortcuts')) {
            return '';
        }
        
        return 'data-keyboard-shortcuts="true"';
    }

    /**
     * Génère les styles inline pour les thèmes
     */
    public static function themeStyles()
    {
        $theme = config('ux.themes.default');
        
        $css = '';
        foreach ($theme as $key => $value) {
            $css .= "--{$key}-color: {$value};";
        }
        
        return $css;
    }
}
