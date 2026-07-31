<?php

namespace App\Mail;

use App\Models\Bid;
use App\Models\RebidToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OutbidRebidMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public RebidToken $rebidToken,
        public Bid $acceptedBid,
    ) {
        $this->rebidToken->loadMissing('domain');
        $this->acceptedBid->loadMissing('domain');
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        $domain = $this->rebidToken->domain;

        return new Envelope(
            from: new Address($domain->mail_from_address, $domain->mail_from_name),
            subject: "A higher offer was accepted for {$domain->hostname}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.outbid-rebid',
            with: [
                'rebidToken' => $this->rebidToken,
                'acceptedBid' => $this->acceptedBid,
                'domain' => $this->rebidToken->domain,
                'rebidUrl' => $this->rebidUrl(),
                'expiresAt' => $this->rebidToken->expires_at,
            ],
        );
    }

    protected function rebidUrl(): string
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme.'://'.$this->rebidToken->domain->hostname.'/offer/verified/'.$this->rebidToken->token;
    }
}
