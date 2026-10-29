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
        private readonly string $purpose,
        private readonly ?string $resetUrl = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject(
                $this->purpose === 'password_reset'
                    ? 'Password Reset Verification'
                    : 'Your verification code'
            )
            ->greeting('Hello,')
            ->line('Your verification code is: ' . $this->code)
            ->line('This code expires in 5 minutes.')
            ->line('Do not share this code with anyone.');

        if ($this->resetUrl !== null) {
            $mail
                ->action('Reset Password', $this->resetUrl)
                ->line('If the button does not work, open this link:')
                ->line($this->resetUrl);
        }

        return $mail
            ->line(
                'If you did not request this code, you can ignore this email.'
            );
    }
}
