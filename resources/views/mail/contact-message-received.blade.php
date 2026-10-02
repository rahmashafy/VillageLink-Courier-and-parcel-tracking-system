<x-mail::message>
# New Contact Message

**From:** {{ $contactMessage->name }}  
**Email:** {{ $contactMessage->email }}  
**Received:** {{ $contactMessage->created_at->format('M d, Y g:i A') }}

---

{{ $contactMessage->message }}

<x-mail::button :url="url('/contact')">
View Contact Page
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
