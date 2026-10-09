@props(['project', 'headingLevel' => 'h3', 'eager' => false])

@php /** @var \App\Models\Project $project */ @endphp

<article {{ $attributes->class('project-card card card-interactive spotlight flex flex-col') }} data-spotlight>
    <div class="media-frame rounded-t-[inherit] border-b border-line">
        @if ($project->coverUrl())
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->coverAlt() }}" width="1600" height="1000"
                 loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async">
        @endif
        @if ($project->status)
            <span class="badge absolute left-3 top-3 z-[2] backdrop-blur">
                @if ($project->is_confidential)<x-icon name="lock" class="size-3" />@endif
                {{ $project->status }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <p class="mono-label flex flex-wrap items-center gap-x-2">
            <span>{{ $project->categoryLabel() }}</span>
            <span aria-hidden="true">·</span>
            <span>{{ $project->typeLabel() }}</span>
        </p>

        <{{ $headingLevel }} class="mt-3 flex items-start justify-between gap-3 text-lg font-semibold leading-snug tracking-tight">
            <a href="{{ $project->url() }}" class="stretched-link">{{ $project->title }}</a>
            <x-icon name="arrow-up-right" class="card-arrow mt-1 size-5 text-ink-subtle" />
        </{{ $headingLevel }}>

        <p class="mt-2.5 line-clamp-3 text-[0.9375rem] muted">{{ $project->short_description }}</p>

        <x-tech-tags :items="$project->techList()" :limit="4" class="mt-auto pt-5" />
    </div>
</article>
