<x-layouts.admin title="Testimonials">
    <x-slot:actions>
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-secondary btn-sm"><x-icon name="plus" class="btn-icon" /> Add one manually</a>
    </x-slot:actions>

    @php $link = session('request_link'); @endphp

    @if ($link)
        @php
            $greeting = $link['name'] ? 'Hi '.strtok($link['name'], ' ').',' : 'Hi,';
            $about = $link['project'] ? ' on '.$link['project'] : '';
            $message = "{$greeting}\n\nI'm updating my portfolio and would really value a short testimonial about our work together{$about}. It takes about two minutes, and nothing is published without your permission:\n\n{$link['url']}\n\nThank you!\n{$site->name()}";
        @endphp
        <section class="card mb-8 grid gap-5 border-accent p-6 sm:p-8" aria-labelledby="new-link-title" role="status">
            <div>
                <h2 id="new-link-title" class="font-semibold">Request link ready</h2>
                <p class="mt-1 text-sm muted">
                    Copy it now — for security only a fingerprint of the link is stored, so it can’t be shown again (you can issue a new one).
                    It works once and expires in {{ \App\Models\Testimonial::REQUEST_LIFETIME_DAYS }} days.
                </p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <input type="text" readonly value="{{ $link['url'] }}" class="input font-mono text-sm" aria-label="Request link">
                <x-copy-button :value="$link['url']" label="Request link" class="shrink-0" />
            </div>
            <div class="field">
                <label for="request-message" class="label">Suggested message</label>
                <textarea id="request-message" readonly rows="8" class="input text-sm">{{ $message }}</textarea>
                <div><x-copy-button :value="$message" label="Message" /></div>
            </div>
        </section>
    @endif

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <section class="card overflow-x-auto" aria-labelledby="list-title">
            <h2 id="list-title" class="border-b border-line px-5 py-4 font-semibold">All testimonials</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">From</th>
                        <th scope="col">About</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($testimonials as $testimonial)
                        <tr>
                            <td>
                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="font-medium hover:text-accent-ink">
                                    {{ $testimonial->author_name ?: 'Awaiting response' }}
                                </a>
                                @if ($testimonial->quote)
                                    <p class="max-w-xs truncate text-sm text-ink-subtle">“{{ $testimonial->excerpt(70) }}”</p>
                                @elseif ($testimonial->request_note)
                                    <p class="max-w-xs truncate text-sm text-ink-subtle">{{ $testimonial->request_note }}</p>
                                @endif
                            </td>
                            <td class="text-sm muted">{{ $testimonial->project?->title ?? 'General' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    <span @class([
                                        'badge',
                                        'badge-success' => $testimonial->isPublic(),
                                        'badge-accent' => $testimonial->status === 'received',
                                    ])>{{ $testimonial->statusLabel() }}</span>
                                    @if ($testimonial->is_featured)<span class="badge">Featured</span>@endif
                                    @if ($testimonial->status !== 'requested' && ! $testimonial->hasConsent())<span class="badge">No consent</span>@endif
                                </div>
                                @if ($testimonial->status === 'requested' && $testimonial->isRequestOpen())
                                    <p class="mt-1 text-xs text-ink-subtle">Expires {{ $testimonial->request_expires_at->diffForHumans() }}</p>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    @if ($testimonial->status === 'requested')
                                        <form method="POST" action="{{ route('admin.testimonials.regenerate', $testimonial) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm">New link</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="icon-btn" aria-label="Edit testimonial from {{ $testimonial->author_name ?: 'pending request' }}"><x-icon name="pencil" /></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center muted">
                                No testimonials yet. Send a request link to a client or colleague →
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <aside class="grid content-start gap-6">
            <form method="POST" action="{{ route('admin.testimonials.request') }}" class="card grid gap-5 p-6" aria-labelledby="request-title">
                @csrf
                <div>
                    <h2 id="request-title" class="font-semibold">Request a testimonial</h2>
                    <p class="mt-1 text-sm muted">Creates a private, single-use form link. Send it by email, WhatsApp, or LinkedIn. Answers arrive here for review.</p>
                </div>
                <x-form.input name="author_name" label="Their name" :optional="true" maxlength="120" id="request-name" hint="Pre-fills the form and personalises the message." />
                <x-form.select name="project_id" label="About which project?" :options="$projects" id="request-project" />
                <x-form.input name="request_note" label="Private note" :optional="true" maxlength="255" id="request-note" placeholder="e.g. Sent via LinkedIn on 7 Oct" />
                <button type="submit" class="btn btn-primary">Create request link</button>
            </form>

            <div class="card p-6 text-sm muted">
                <h2 class="font-semibold text-ink">How publishing works</h2>
                <ol class="mt-3 grid list-decimal gap-2 pl-5">
                    <li>Responses arrive as <strong class="text-ink">Needs review</strong>, with the person’s consent recorded.</li>
                    <li>Edit typos if needed, then set the status to <strong class="text-ink">Published</strong>.</li>
                    <li>Featured testimonials appear first on the home page; ones linked to a project also appear on its case study.</li>
                </ol>
                <p class="mt-3">Only publish words the person actually gave you, with their permission.</p>
            </div>
        </aside>
    </div>
</x-layouts.admin>
