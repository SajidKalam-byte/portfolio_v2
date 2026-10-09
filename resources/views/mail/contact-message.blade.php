{{-- Plain-text email: raw output is intentional (no HTML is rendered, so entities would show literally). --}}
New message from your portfolio contact form

From:    {!! $contactMessage->name !!} <{!! $contactMessage->email !!}>
Subject: {!! $contactMessage->subject ?: '(none)' !!}
Sent:    {{ $contactMessage->created_at?->toDayDateTimeString() }}

----------------------------------------------------------------------

{!! $contactMessage->message !!}

----------------------------------------------------------------------

Reply directly to this email to respond.
@if (Route::has('admin.messages.show'))
The message is also saved in the admin area:
{{ route('admin.messages.show', $contactMessage) }}
@endif
