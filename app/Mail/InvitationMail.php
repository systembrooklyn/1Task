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
                    ->view('emails.invitation')
                    ->with([
                        'inviter' => $this->invitation->inviter ? $this->invitation->inviter : "1Task",
                        'companyName' => $this->invitation->company ? $this->invitation->company->name : 'No Company',
                        'token' => $this->invitation->token,               
                        'expiresAt' => $this->invitation->expires_at,
                        'company_id' =>$this->invitation->company_id  
                    ]);
    }
}
