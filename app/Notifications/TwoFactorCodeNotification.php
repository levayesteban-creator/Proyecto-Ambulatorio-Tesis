<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
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
            ->subject('Código de Verificación - Consultorio "El Chaparro de Guanta"')
            ->view('emails.two-factor-code', [
                'code' => $this->code,
                'notifiable' => $notifiable,
            ]);
    }
}
