<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $newEmail;
    public string $code;

    public function __construct(string $newEmail, string $code)
    {
        $this->newEmail = $newEmail;
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'YouFocus - Código de verificação',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change-code',
        );
    }
}

