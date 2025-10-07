<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishUXAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ux:publish {--force : Force overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish UX modern assets to public directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Publishing UX modern assets...');

        // Créer le dossier css s'il n'existe pas
        if (!File::exists(public_path('css'))) {
            File::makeDirectory(public_path('css'), 0755, true);
        }

        // Copier le fichier CSS
        $source = resource_path('css/ux-modern.css');
        $destination = public_path('css/ux-modern.css');

        if (File::exists($source)) {
            if (File::exists($destination) && !$this->option('force')) {
                $this->warn('CSS file already exists. Use --force to overwrite.');
            } else {
                File::copy($source, $destination);
                $this->info('✅ UX modern CSS published successfully!');
            }
        } else {
            $this->error('❌ Source CSS file not found: ' . $source);
        }

        // Copier le fichier de configuration
        $configSource = config_path('ux.php');
        if (File::exists($configSource)) {
            $this->info('✅ UX configuration file found.');
        } else {
            $this->warn('⚠️  UX configuration file not found. Run: php artisan vendor:publish --tag=ux-config');
        }

        $this->info('🎉 UX assets publishing completed!');
    }
}
