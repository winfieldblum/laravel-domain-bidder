<?php

namespace App\Mail;

use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BidVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Bid $bid)
    {
        $this->bid->loadMissing('domain');
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        $domain = $this->bid->domain;

        return new Envelope(
            from: new Address($domain->mail_from_address, $domain->mail_from_name),
            subject: "Verify your {$domain->hostname} offer",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.bid-verification',
            with: [
                'bid' => $this->bid,
                'domain' => $this->bid->domain,
                'verificationUrl' => $this->verificationUrl(),
            ],
        );
    }

    protected function verificationUrl(): string
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme.'://'.$this->bid->domain->hostname.'/verify/'.$this->bid->verification_token;
    }
}
