<x-layouts.admin title="Messages">
    <nav aria-label="Filter messages" class="flex flex-wrap gap-2">
        <a href="{{ route('admin.messages.index') }}" class="chip" @if (! $activeStatus) aria-current="page" @endif>
            All <span class="chip-count">{{ $counts->sum() }}</span>
        </a>
        @foreach (\App\Models\ContactMessage::STATUSES as $key => $label)
            <a href="{{ route('admin.messages.index', ['status' => $key]) }}" class="chip" @if ($activeStatus === $key) aria-current="page" @endif>
                {{ $label }} <span class="chip-count">{{ $counts->get($key, 0) }}</span>
            </a>
        @endforeach
    </nav>

    <div class="card mt-6 overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">From</th>
                    <th scope="col">Subject</th>
                    <th scope="col">Received</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr @class(['font-medium' => $message->status === 'pending'])>
                        <td>
                            <a href="{{ route('admin.messages.show', $message) }}" class="hover:text-accent-ink">{{ $message->name }}</a>
                            <p class="text-sm font-normal text-ink-subtle">{{ $message->email }}</p>
                        </td>
                        <td class="max-w-xs truncate text-sm">{{ $message->subject ?: \Illuminate\Support\Str::limit($message->message, 60) }}</td>
                        <td class="whitespace-nowrap text-sm text-ink-subtle">
                            <time datetime="{{ $message->created_at->toAtomString() }}" title="{{ $message->created_at->toDayDateTimeString() }}">{{ $message->created_at->diffForHumans() }}</time>
                        </td>
                        <td><span @class(['badge', 'badge-accent' => $message->status === 'pending'])>{{ $message->statusLabel() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-10 text-center muted">No messages.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $messages->links('pagination.portfolio') }}
</x-layouts.admin>
