{{-- Case-study body. Rendered to a string so section numbers can be added in order. --}}
@php
    /** @var \App\Models\Project $project */
    $paragraphs = fn (?string $text) => array_filter(array_map('trim', preg_split('/\n\s*\n/', (string) $text) ?: []));
@endphp

<section aria-labelledby="overview">
    <h2 id="overview">Overview</h2>
    @foreach ($paragraphs($project->description) as $paragraph)
        <p>{{ $paragraph }}</p>
    @endforeach
    @foreach ($paragraphs($project->long_description) as $paragraph)
        <p>{{ $paragraph }}</p>
    @endforeach
</section>

@if (! empty($project->responsibilities))
    <section aria-labelledby="contributions">
        <h2 id="contributions">My contributions</h2>
        <ul class="check-list">
            @foreach ($project->responsibilities as $item)
                <li><x-icon name="check" class="size-3.5" /><span>{{ $item }}</span></li>
            @endforeach
        </ul>
    </section>
@endif

{!! $caseStudy['html'] !!}

@if ($project->images->isNotEmpty())
    <section aria-labelledby="gallery">
        <h2 id="gallery">Gallery</h2>
        <div class="mt-6 grid gap-6">
            @foreach ($project->images as $image)
                <figure class="card overflow-hidden">
                    <img src="{{ $image->url() }}" alt="{{ $image->alt }}" loading="lazy" decoding="async" class="w-full"
                         @if ($image->width) width="{{ $image->width }}" height="{{ $image->height }}" @endif>
                    @if ($image->caption)
                        <figcaption class="border-t border-line px-5 py-3 text-sm text-ink-subtle">{{ $image->caption }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </section>
@endif

@if ($testimonials->isNotEmpty())
    <section aria-labelledby="feedback">
        <h2 id="feedback">{{ $testimonials->every(fn ($t) => $t->relationship === 'client') ? 'Client feedback' : 'Feedback' }}</h2>
        <div class="mt-6 grid gap-6">
            @foreach ($testimonials as $testimonial)
                <x-testimonial-card :testimonial="$testimonial" :show-project="false" :size="$loop->count === 1 ? 'large' : 'default'" />
            @endforeach
        </div>
    </section>
@endif
