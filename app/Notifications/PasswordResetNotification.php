<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
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
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour '.$notifiable->first_name.',')
            ->line('Vous avez demandé la réinitialisation de votre mot de passe.')
            ->line('Voici votre code de vérification à 6 chiffres :')
            ->line('**'.$this->code.'**')
            ->line('Ce code expire dans 10 minutes.')
            ->line('Saisissez ce code dans le formulaire de réinitialisation VisionCash.')
            ->salutation('Cordialement, l\'équipe VisionCash');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
