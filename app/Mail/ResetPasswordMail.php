<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance
     */
    public function __construct(
        public User $user,
        public string $token
    ) {}

    /**
     * Get the message envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SmarTern: Reset Your Password',
        );
    }

    /**
     * Get the message content definition
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
            with: [
                'resetUrl' => route('password.reset', [
                    'token' => $this->token,
                    'email' => $this->user->email,
                ]),
            ],
        );
    }
}
