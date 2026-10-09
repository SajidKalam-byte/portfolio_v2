@props(['project', 'reverse' => false, 'label' => 'Featured case study', 'testimonial' => null])

@php
    /** @var \App\Models\Project $project */
    /** @var \App\Models\Testimonial|null $testimonial */
@endphp

<article {{ $attributes->class('feature-card card card-interactive spotlight grid lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]') }} data-spotlight>
    <div @class([
        'media-frame border-b border-line lg:aspect-auto lg:border-b-0',
        'rounded-t-[inherit] lg:rounded-l-[inherit] lg:rounded-tr-none lg:border-r' => ! $reverse,
        'rounded-t-[inherit] lg:order-2 lg:rounded-r-[inherit] lg:rounded-tl-none lg:border-l' => $reverse,
    ])>
        @if ($project->coverUrl())
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->coverAlt() }}" width="1600" height="1000" loading="lazy" decoding="async">
        @endif
    </div>

    <div class="flex flex-col p-6 sm:p-8 lg:p-10">
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge badge-accent">{{ $label }}</span>
            <span class="badge">{{ $project->categoryLabel() }}</span>
        </div>

        <h3 class="mt-5 text-2xl font-semibold leading-tight tracking-tight sm:text-[1.875rem]">
            <a href="{{ $project->url() }}" class="stretched-link">{{ $project->title }}</a>
        </h3>

        <p class="mt-4 muted">{{ $project->short_description }}</p>

        <dl class="fact-list mt-7 text-sm">
            <div><dt>Role</dt><dd>{{ $project->role }}</dd></div>
            @if ($project->period)
                <div><dt>Period</dt><dd>{{ $project->period }}</dd></div>
            @endif
            @if ($project->status)
                <div><dt>Status</dt><dd>{{ $project->status }}</dd></div>
            @endif
        </dl>

        @if (! empty($project->responsibilities))
            <ul class="check-list mt-6 text-sm muted">
                @foreach (array_slice($project->responsibilities, 0, $testimonial ? 2 : 4) as $item)
                    <li><x-icon name="check" class="size-3.5" /><span>{{ $item }}</span></li>
                @endforeach
            </ul>
        @endif

        @if ($testimonial)
            <figure class="card-quote mt-6">
                <blockquote>&ldquo;{{ $testimonial->excerpt(170) }}&rdquo;</blockquote>
                <figcaption class="mono-label mt-2">
                    — {{ collect([$testimonial->displayName(), $testimonial->byline()])->filter()->implode(', ') }}
                </figcaption>
            </figure>
        @endif

        <x-tech-tags :items="$project->techList()" :limit="7" class="mt-7" />

        <p class="mt-8 inline-flex items-center gap-2 text-sm font-medium text-accent-ink" aria-hidden="true">
            Read the case study <x-icon name="arrow-right" class="size-4" />
        </p>
    </div>
</article>
