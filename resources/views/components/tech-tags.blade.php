@props(['items' => [], 'limit' => null, 'label' => 'Technologies'])

@php
    $items = array_values($items ?? []);
    $shown = $limit ? array_slice($items, 0, $limit) : $items;
    $hidden = count($items) - count($shown);
@endphp

@if ($items)
    <ul {{ $attributes->class('plain flex flex-wrap gap-1.5') }} aria-label="{{ $label }}">
        @foreach ($shown as $item)
            <li class="tag">{{ $item }}</li>
        @endforeach
        @if ($hidden > 0)
            <li class="tag"><span aria-hidden="true">+{{ $hidden }}</span><span class="sr-only">and {{ $hidden }} more</span></li>
        @endif
    </ul>
@endif
