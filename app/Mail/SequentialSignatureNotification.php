<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SequentialSignatureNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Document $document;
    public User $signer;
    public User $previousSigner;
    public float $progress;
    public bool $isCompleted;

    /**
     * Create a new message instance.
     */
    public function __construct(Document $document, User $signer, User $previousSigner = null, bool $isCompleted = false)
    {
        $this->document = $document;
        $this->signer = $signer;
        $this->previousSigner = $previousSigner;
        $this->progress = $document->getSignatureProgress();
        $this->isCompleted = $isCompleted;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isCompleted 
            ? 'Document entièrement signé - ' . $this->document->document_name
            : 'Votre tour de signer - ' . $this->document->document_name;

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sequential-signature',
            with: [
                'document' => $this->document,
                'signer' => $this->signer,
                'previousSigner' => $this->previousSigner,
                'progress' => $this->progress,
                'isCompleted' => $this->isCompleted,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
