<?php

namespace App\Mail;

use App\Models\OnboardingApplicant;
use App\Models\Office;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfficeAssignmentFinalizedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance
     */
    public function __construct(
        public OnboardingApplicant $applicant,
        public Office $office
    ) {}

    /**
     * Get the message envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SmarTern: Office Assignment Finalized',
        );
    }

    /**
     * Get the message content definition
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.office-assignment-finalized',
        );
    }

    /**
     * Get the attachments for the message
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
