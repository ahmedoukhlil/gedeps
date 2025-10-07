<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentRefusedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Document $document;
    public string $refusalComment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, string $refusalComment)
    {
        $this->document = $document;
        $this->refusalComment = $refusalComment;
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
            ->subject('Document refusé - ' . $this->document->filename_original)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre document "' . $this->document->filename_original . '" a été refusé.')
            ->line('Type de document : ' . $this->document->type_name)
            ->line('Date de refus : ' . $this->document->updated_at->format('d/m/Y H:i'))
            ->line('Commentaire : ' . $this->refusalComment)
            ->action('Consulter le document', route('documents.download', $this->document))
            ->line('Vous pouvez modifier et soumettre à nouveau votre document si nécessaire.');
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
            'action' => 'refused',
            'refused_at' => $this->document->updated_at,
            'comment' => $this->refusalComment,
        ];
    }
}
