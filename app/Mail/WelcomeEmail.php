<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailSubject;
    public $mailMessage;

    public function __construct($message, $subject)
    {
        $this->mailSubject = $subject;
        $this->mailMessage = $message;
    }

    public function envelope(): Envelope
    {
        // Envelpe means project title
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.welcome_email',
        );
    }
    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
