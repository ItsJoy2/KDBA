<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $purpose
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your verification code')
            ->greeting('Hello,')
            ->line('Your verification code is: ' . $this->code)
            ->line('This code expires in 5 minutes.')
            ->line('Do not share this code with anyone.')
            ->line('If you did not request this code, you can ignore this email.');
    }
}