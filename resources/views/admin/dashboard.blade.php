<x-layouts.admin title="Dashboard">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Published projects', 'value' => $counts['published'].' / '.$counts['projects'], 'route' => 'admin.projects.index'],
            ['label' => 'Testimonials (to review)', 'value' => $counts['testimonials'].' ('.$counts['testimonialsToReview'].')', 'route' => 'admin.testimonials.index'],
            ['label' => 'Roles & education', 'value' => $counts['experiences'].' · '.$counts['educations'], 'route' => 'admin.experiences.index'],
            ['label' => 'New messages', 'value' => $counts['unread'], 'route' => 'admin.messages.index'],
        ] as $stat)
            <a href="{{ route($stat['route']) }}" class="card card-interactive p-5">
                <p class="mono-label">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $stat['value'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
        <section class="card p-6" aria-labelledby="recent-title">
            <div class="flex items-center justify-between gap-3">
                <h2 id="recent-title" class="font-semibold">Recent messages</h2>
                <a href="{{ route('admin.messages.index') }}" class="link-accent text-sm">View all</a>
            </div>
            @if ($recentMessages->isEmpty())
                <p class="mt-4 text-sm muted">No messages yet.</p>
            @else
                <ul class="mt-4 divide-y divide-line">
                    @foreach ($recentMessages as $message)
                        <li class="py-3">
                            <a href="{{ route('admin.messages.show', $message) }}" class="group flex items-start justify-between gap-3">
                                <span class="min-w-0">
                                    <span class="block truncate font-medium group-hover:text-accent-ink">{{ $message->subject ?: 'Message from '.$message->name }}</span>
                                    <span class="block truncate text-sm text-ink-subtle">{{ $message->name }} · {{ $message->created_at->diffForHumans() }}</span>
                                </span>
                                <span @class(['badge shrink-0', 'badge-accent' => $message->status === 'pending'])>{{ $message->statusLabel() }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="card p-6" aria-labelledby="health-title">
            <h2 id="health-title" class="font-semibold">Deployment checks</h2>
            <ul class="mt-4 grid gap-3 text-sm">
                @foreach ($checks as $check)
                    <li class="flex gap-3">
                        <x-icon :name="$check['ok'] ? 'check-circle' : 'alert'" @class(['mt-0.5 size-4', 'text-success' => $check['ok'], 'text-warning' => ! $check['ok']]) />
                        <div>
                            <p class="font-medium">{{ $check['label'] }} <span class="sr-only">— {{ $check['ok'] ? 'OK' : 'needs attention' }}</span></p>
                            @unless ($check['ok'])
                                <p class="text-ink-subtle">{{ $check['hint'] }}</p>
                            @endunless
                        </div>
                    </li>
                @endforeach
            </ul>

            <h3 class="mt-7 font-semibold">Caches</h3>
            <p class="mt-1 text-sm text-ink-subtle">For hosts without SSH. Content edits here clear the content cache automatically.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach (['clear-cache' => 'Clear content cache', 'optimize' => 'Optimize', 'clear-optimization' => 'Clear all caches'] as $action => $label)
                    <form method="POST" action="{{ route('admin.maintenance', $action) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">{{ $label }}</button>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.admin>
