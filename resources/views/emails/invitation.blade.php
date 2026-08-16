<x-mail::message>
# Independence Day Attendance Confirmation

Dear {{ $contactName }},

You are receiving this on behalf of **{{ $vipName }}** for the upcoming Independence Day event.

Please confirm attendance using the secure link below:

<x-mail::button :url="$rsvpUrl">
Confirm Attendance
</x-mail::button>

If the button above doesn't work, copy and paste this link into your browser:

{{ $rsvpUrl }}

This link is unique to you — please do not forward it.

Thanks,<br>
--
</x-mail::message>