<?php

namespace App\Listeners;

use App\Events\DocumentRefused;
use App\Notifications\DocumentRefusedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDocumentRefusedNotification implements ShouldQueue
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
    public function handle(DocumentRefused $event): void
    {
        $event->document->uploader->notify(
            new DocumentRefusedNotification($event->document, $event->signature->comment_manager ?? '')
        );
    }
}
