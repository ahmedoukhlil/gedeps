<?php

namespace App\Events;

use App\Models\Document;
use App\Models\DocumentSignature;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentRefused
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Document $document;
    public DocumentSignature $signature;

    /**
     * Create a new event instance.
     */
    public function __construct(Document $document, DocumentSignature $signature)
    {
        $this->document = $document;
        $this->signature = $signature;
    }
}
