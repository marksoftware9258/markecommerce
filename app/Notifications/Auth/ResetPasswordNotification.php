<?php

namespace App\Notifications\Auth;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly Carbon $expiresAt
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = config('app.frontend_url', config('app.url'))
            . '/reset-password?token=' . $this->token
            . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting("Hello {$notifiable->name}!")
            ->line('You are receiving this email because we received a password reset request.')
            ->action('Reset Password', $url)
            ->line("This link expires at {$this->expiresAt->format('H:i')} UTC ({$this->expiresAt->diffForHumans()}).")
            ->line('If you did not request a password reset, no further action is required.');
    }
}
