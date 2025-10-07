<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\UXHelper;

class UXServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('ux', function ($app) {
            return new UXHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publier les assets UX
        $this->publishes([
            __DIR__.'/../../resources/css/ux-modern.css' => public_path('css/ux-modern.css'),
        ], 'ux-assets');

        // Publier la configuration
        $this->publishes([
            __DIR__.'/../../config/ux.php' => config_path('ux.php'),
        ], 'ux-config');
    }
}
