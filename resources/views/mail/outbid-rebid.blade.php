<x-mail::message>
# A higher offer was accepted

A verified offer of **${{ number_format($acceptedBid->amount) }}** has been accepted for **{{ $domain->hostname }}**.

Your previous verified offer is now below that amount. You can place a new bid without verifying your email again using the secure link below.

This link expires **{{ $expiresAt->timezone(config('app.timezone'))->format('M j, Y g:i A T') }}** and can only be used once.

<x-mail::button :url="$rebidUrl">
Place a new verified bid
</x-mail::button>

Thanks,<br>
{{ $domain->mail_from_name }}
</x-mail::message>
