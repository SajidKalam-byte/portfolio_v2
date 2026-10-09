@props(['testimonial', 'size' => 'default', 'showProject' => true])

@php
    /** @var \App\Models\Testimonial $testimonial */
    $project = $showProject ? $testimonial->project : null;
@endphp

<figure {{ $attributes->class(['testimonial card', 'testimonial-lg' => $size === 'large', 'testimonial-sm' => $size === 'small']) }}>
    <span class="testimonial-mark" aria-hidden="true">&ldquo;</span>

    <blockquote class="testimonial-quote">
        @foreach ($testimonial->paragraphs() as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach
    </blockquote>

    <figcaption class="testimonial-author">
        @if ($testimonial->avatarUrl())
            <img src="{{ $testimonial->avatarUrl() }}" alt="" class="avatar" width="48" height="48" loading="lazy" decoding="async">
        @else
            <span class="avatar" aria-hidden="true">{{ $testimonial->initials() }}</span>
        @endif

        <span class="min-w-0 flex-1">
            <cite class="block font-semibold not-italic text-ink">{{ $testimonial->displayName() }}</cite>
            @if ($testimonial->byline())
                <span class="block text-sm text-ink-muted">{{ $testimonial->byline() }}</span>
            @endif
            @if ($testimonial->relationshipLabel() || $project)
                <span class="mono-label mt-1 block">
                    {{ $testimonial->relationshipLabel() }}
                    @if ($project)
                        <span aria-hidden="true">·</span>
                        <a href="{{ $project->url() }}" class="link-accent relative z-[2]">{{ $project->title }}</a>
                    @endif
                </span>
            @endif
        </span>

        @if ($testimonial->source_url)
            <a href="{{ $testimonial->source_url }}" class="icon-btn relative z-[2] shrink-0" rel="noopener nofollow" target="_blank"
               aria-label="View the original testimonial from {{ $testimonial->displayName() }} (opens in a new tab)">
                <x-icon :name="str_contains($testimonial->source_url, 'linkedin.com') ? 'linkedin' : 'external'" class="size-4" />
            </a>
        @endif
    </figcaption>
</figure>
