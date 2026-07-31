<x-mail::message>
# New verified offer

A new verified offer was received for **{{ $domain->hostname }}**.

- **Amount:** ${{ number_format($bid->amount) }}
- **From:** {{ $bid->name }} ({{ $bid->email }})

<x-mail::button :url="$adminUrl">
View in admin
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
