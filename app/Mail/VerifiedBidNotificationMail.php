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

class VerifiedBidNotificationMail extends Mailable implements ShouldQueue
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
            subject: "New Verified Offer: \${$this->bid->amount} for {$domain->hostname}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.verified-bid-notification',
            with: [
                'bid' => $this->bid,
                'domain' => $this->bid->domain,
                'adminUrl' => url('/admin/bids'),
            ],
        );
    }
}
