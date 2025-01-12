<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EditCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $editCode;

    /**
     * Create a new message instance.
     */
    public function __construct($editCode)
    {
        $this->editCode = $editCode;
    }
    public function build()
    {
        return $this->subject('Your Digital Card Edit Code')
                    ->view('emails.edit_code')
                    ->with(['editCode' => $this->editCode]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Edit Code Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'views.emials.edit_code.blade.php',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
