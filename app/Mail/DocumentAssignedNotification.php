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

class DocumentAssignedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Document $document;
    public User $signer;
    public User $agent;

    /**
     * Create a new message instance.
     */
    public function __construct(Document $document, User $signer, User $agent)
    {
        $this->document = $document;
        $this->signer = $signer;
        $this->agent = $agent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📄 Nouveau document à signer',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document-assigned',
            with: [
                'document' => $this->document,
                'signer' => $this->signer,
                'agent' => $this->agent,
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
