<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token
    ) {
        //
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
            ->subject('Vérification de votre email')
            ->greeting('Bonjour ' . $notifiable->first_name . ',')
            ->line('Merci de vous être inscrit sur VisionCash.')
            ->line('Veuillez vérifier votre adresse email en utilisant le token suivant:')
            ->line('**' . $this->token . '**')
            ->line('Ce token expire dans 60 minutes.')
            ->line('Utilisez ce token avec l\'endpoint de vérification d\'email.')
            ->salutation('Cordialement, l\'équipe VisionCash');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
