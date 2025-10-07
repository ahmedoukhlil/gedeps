<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentSignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Document $document;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Document signé - ' . $this->document->filename_original)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre document "' . $this->document->filename_original . '" a été signé avec succès.')
            ->line('Type de document : ' . $this->document->type_name)
            ->line('Date de signature : ' . $this->document->updated_at->format('d/m/Y H:i'))
            ->action('Télécharger le document signé', route('documents.download', $this->document))
            ->line('Merci d\'utiliser notre système de gestion documentaire.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_name' => $this->document->filename_original,
            'action' => 'signed',
            'signed_at' => $this->document->updated_at,
        ];
    }
}
