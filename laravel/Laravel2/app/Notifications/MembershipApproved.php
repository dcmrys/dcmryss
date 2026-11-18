<?php

namespace App\Notifications;

use App\Models\MembershipApplication;
use App\Models\MembershipTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MembershipApplication $application,
        public MembershipTransaction $transaction,
        public string $passwordSetupToken,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your membership application was approved')
            ->greeting('Hello '.$this->application->full_name.',')
            ->line('Your membership application has been approved.')
            ->line('Transaction reference: '.$this->transaction->reference_no)
            ->line('Click the link below to set up your password.')
            ->action('Set up your password', route('password.setup', [
                'token' => $this->passwordSetupToken,
                'email' => $this->application->email,
            ]))
            ->line('This link expires in '.config('auth.passwords.users.expire').' minutes and can be used once.')
            ->line('After setting your password, return to the sign-in page to access your account.')
            ->salutation(config('app.name'));
    }
}
