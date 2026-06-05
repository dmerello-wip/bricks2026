<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription) {}

    public function build()
    {
        return $this->subject('Iscrizione ricevuta - Bricks Music Festival')
            ->view('emails.subscription_confirmation', [
                'subscription' => $this->subscription,
            ]);
    }
}
