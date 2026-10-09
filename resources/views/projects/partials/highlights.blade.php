@php /** @var \App\Models\Project $project */ @endphp

<section aria-labelledby="at-a-glance">
    <h2 id="at-a-glance">At a glance</h2>
    <ul class="feature-grid">
        @foreach ($project->highlights as $highlight)
            <li><x-icon name="check-circle" class="size-4" /><span>{{ $highlight }}</span></li>
        @endforeach
    </ul>
</section>
