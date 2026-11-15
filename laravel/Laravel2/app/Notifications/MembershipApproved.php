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
            ->line('Amount due: ₱'.number_format((float) $this->transaction->amount_due, 2))
            ->line('Your membership becomes active after the transaction is paid and approved.')
            ->line('Your member profile is ready. Visit the sign-in page to access your account.')
            ->line('If you have not set a password yet, submit your email without a password on the sign-in page. We will email you a secure setup link.')
            ->action('Go to sign in', route('login'))
            ->salutation(config('app.name'));
    }
}
