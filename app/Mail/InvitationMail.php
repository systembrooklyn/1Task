<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    public $invitation;

    // Inject the invitation model into the mailable
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    // Build the email
    public function build()
    {
        return $this->subject('You have been invited to join the platform!')
                    ->view('emails.invitation');
    }
}
