<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public JobApplication $application;

    /**
     * Create a new message instance.
     */
    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->application->status->value) {
            'screening' => 'Application Update: Profile Reviewing',
            'interview' => 'Congratulations! Interview Invitation - AIMANOVA',
            'offer' => 'Good News! Job Offer from AIMANOVA',
            'hired' => 'Welcome to AIMANOVA! You are Hired',
            'rejected' => 'Application Update - ' . $this->application->job->title,
            default => 'Application Status Update - AIMANOVA',
        };

        return new Envelope(
            subject: $subject,
        );
    }


    public function content(): Content
    {
        $headerTitle = match($this->application->status->value) {

            'screening' => 'Profile Under Review',
            'interview' => 'Interview Invitation',
            'offer' => 'Employment Offer',
            'hired' => 'Welcome Aboard!',
            'rejected' => 'Application Update',
            default => 'Application Update',
        };

        return new Content(
            view: 'emails.applicant-status-update',
            with: [
                'name' => $this->application->candidate->name,
                'jobTitle' => $this->application->job->title,
                'status' => $this->application->status,
                'statusLabel' => $this->application->status->label(),
                'headerTitle' => $headerTitle,
            ],
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
