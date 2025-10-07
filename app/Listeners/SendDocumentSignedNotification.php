<?php

namespace App\Listeners;

use App\Events\DocumentSigned;
use App\Notifications\DocumentSignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDocumentSignedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DocumentSigned $event): void
    {
        $event->document->uploader->notify(
            new DocumentSignedNotification($event->document)
        );
    }
}
