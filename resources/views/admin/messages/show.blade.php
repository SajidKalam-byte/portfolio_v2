<x-layouts.admin title="Message" :heading="$message->subject ?: 'Message from '.$message->name">
    <x-slot:actions>
        <a href="{{ route('admin.messages.index') }}" class="btn btn-ghost btn-sm"><x-icon name="arrow-left" class="btn-icon" /> All messages</a>
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <article class="card p-6 sm:p-8">
            <dl class="fact-list text-sm">
                <div><dt>From</dt><dd>{{ $message->name }}</dd></div>
                <div><dt>Email</dt><dd><a class="link-accent" href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Your message')) }}">{{ $message->email }}</a></dd></div>
                <div><dt>Received</dt><dd>{{ $message->created_at->toDayDateTimeString() }}</dd></div>
            </dl>
            {{-- Escaped output + preserved line breaks: visitor input is never rendered as HTML. --}}
            <div class="mt-8 whitespace-pre-line leading-relaxed">{{ $message->message }}</div>
        </article>

        <aside class="grid content-start gap-4">
            <form method="POST" action="{{ route('admin.messages.update', $message) }}" class="card grid gap-4 p-6">
                @csrf @method('PATCH')
                <x-form.select name="status" label="Status" :options="\App\Models\ContactMessage::STATUSES" :value="$message->status" />
                <button type="submit" class="btn btn-secondary btn-sm">Update status</button>
            </form>

            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Your message')) }}" class="btn btn-primary">
                <x-icon name="mail" class="btn-icon" /> Reply by email
            </a>

            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" data-confirm="Delete this message permanently?">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm w-full"><x-icon name="trash" class="btn-icon" /> Delete message</button>
            </form>
        </aside>
    </div>
</x-layouts.admin>
