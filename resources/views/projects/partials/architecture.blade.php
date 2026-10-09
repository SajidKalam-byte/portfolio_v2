@php
    /** @var \App\Models\Project $project */
    $architecture = $project->architecture ?? [];
    $layers = $project->architectureLayers();
    $stackId = 'arch-stack-'.$project->id;
@endphp

<section aria-labelledby="architecture">
    <h2 id="architecture">{{ $architecture['heading'] ?? 'Architecture overview' }}</h2>
    @if (! empty($architecture['intro']))
        <p>{{ $architecture['intro'] }}</p>
    @endif

    <div class="mt-6 flex items-center justify-between gap-4">
        <p class="mono-label">{{ count($layers) }} layers · select a layer for details</p>
        <button type="button" class="btn btn-ghost btn-sm" data-arch-toggle aria-controls="{{ $stackId }}" aria-expanded="false" hidden>Expand all</button>
    </div>

    <ol class="arch-stack" id="{{ $stackId }}">
        @foreach ($layers as $layer)
            <li class="arch-layer">
                <details @if ($loop->first) open @endif>
                    <summary>
                        <span class="arch-index" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span>
                            @if (! empty($layer['layer']))
                                <span class="arch-kicker">{{ $layer['layer'] }}</span>
                            @endif
                            <span class="arch-title">{{ $layer['title'] ?? '' }}</span>
                        </span>
                        <x-icon name="chevron-down" class="arch-chevron size-4" />
                    </summary>
                    <div class="arch-body">
                        @if (! empty($layer['description']))
                            <p>{{ $layer['description'] }}</p>
                        @endif
                        @if (! empty($layer['items']))
                            <x-tech-tags :items="$layer['items']" :label="($layer['title'] ?? 'Layer').' components'" class="mt-3" />
                        @endif
                    </div>
                </details>
            </li>
        @endforeach
    </ol>
</section>
