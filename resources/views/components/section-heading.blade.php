@props(['index' => null, 'eyebrow', 'titleId' => null, 'as' => 'h2'])

<div {{ $attributes->class('max-w-3xl') }}>
    <p class="eyebrow">
        @if ($index)
            <span class="eyebrow-index">{{ $index }}</span>
            <span class="eyebrow-rule" aria-hidden="true"></span>
        @endif
        {{ $eyebrow }}
    </p>
    <{{ $as }} @if ($titleId) id="{{ $titleId }}" @endif class="title mt-5">{{ $title }}</{{ $as }}>
    @if ($slot->isNotEmpty())
        <div class="lead mt-5 max-w-2xl">{{ $slot }}</div>
    @endif
</div>
