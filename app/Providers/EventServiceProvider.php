<?php

namespace App\Providers;

use App\Events\DocumentRefused;
use App\Events\DocumentSigned;
use App\Events\DocumentUploaded;
use App\Listeners\SendDocumentSignedNotification;
use App\Listeners\SendDocumentRefusedNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        DocumentUploaded::class => [
            // Ajouter des listeners si nécessaire
        ],
        DocumentSigned::class => [
            SendDocumentSignedNotification::class,
        ],
        DocumentRefused::class => [
            SendDocumentRefusedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
