<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordSetupRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresIn = config('auth.passwords.users.expire');

        return (new MailMessage)
            ->subject('Set up your member account password')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A sign-in attempt found that your member account still needs a password.')
            ->action('Set up your password', route('password.setup', [
                'token' => $this->token,
                'email' => $notifiable->email,
            ]))
            ->line("This link expires in {$expiresIn} minutes and can be used once.")
            ->salutation(config('app.name'));
    }
}
