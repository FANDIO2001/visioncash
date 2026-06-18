<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Vérification de votre email')
            ->greeting('Bonjour '.$notifiable->first_name.',')
            ->line('Merci de vous être inscrit sur VisionCash.')
            ->line('Voici votre code de vérification à 6 chiffres :')
            ->line('**'.$this->code.'**')
            ->line('Ce code expire dans 60 minutes.')
            ->line('Saisissez ce code pour activer votre compte.')
            ->salutation('Cordialement, l\'équipe VisionCash');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
