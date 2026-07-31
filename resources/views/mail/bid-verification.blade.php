<x-mail::message>
# Verify your offer

Thank you for your offer of **${{ number_format($bid->amount) }}** for **{{ $domain->hostname }}**.

<x-mail::button :url="$verificationUrl">
Verify your email
</x-mail::button>

Once verified, the domain owner will review your bid.

Thanks,<br>
{{ $domain->mail_from_name }}
</x-mail::message>
