<?php

namespace App\Mail;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfficeAnnouncementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Announcement $announcement,
        public User $recipient
    ) {}

    public function envelope(): Envelope
    {
        $senderName = 'SmarTern Administrator';
        $officeName = 'System Administration';
        if ($this->announcement->user && $this->announcement->user->supervisorDetails) {
            $senderName = $this->announcement->user->supervisorDetails->first_name.' '.$this->announcement->user->supervisorDetails->last_name;
            $officeName = $this->announcement->user->supervisorDetails->office->name ?? 'Office';
        }

        return new Envelope(
            subject: "{$officeName} - {$this->announcement->title}: {$senderName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
