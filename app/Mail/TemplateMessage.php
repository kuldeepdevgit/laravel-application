<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemplateMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, string>  $tokens
     */
    public function __construct(
        public User $member,
        public EmailTemplate $template,
        public SmtpSetting $smtp,
        public array $tokens,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->smtp->from_address, $this->smtp->from_name),
            subject: $this->template->renderSubject($this->tokens),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.member-joined',
            text: 'mail.member-joined-text',
            with: [
                'body' => $this->template->renderBody($this->tokens),
            ],
        );
    }
}
