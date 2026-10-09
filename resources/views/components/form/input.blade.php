@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'optional' => false,
    'textarea' => false,
    'bag' => 'default',
    'id' => null,
    'counter' => false,
])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id ??= 'field-'.\Illuminate\Support\Str::slug($key);
    $error = $errors->getBag($bag)->first($key);
    $current = old($key, $value);
    $describedBy = collect([
        $hint ? "{$id}-hint" : null,
        $counter ? "{$id}-counter" : null,
        $error ? "{$id}-error" : null,
    ])->filter()->implode(' ');
    $shared = [
        'id' => $id,
        'name' => $name,
        'data-label' => $label,
        'aria-describedby' => $describedBy ?: null,
        'aria-invalid' => $error ? 'true' : null,
        'data-maxlength-counter' => $counter ? "{$id}-counter" : null,
    ];
@endphp

<div class="field">
    <div class="flex items-baseline justify-between gap-3">
        <label for="{{ $id }}" class="label">
            {{ $label }}
            @if ($optional)
                <span class="label-optional">(optional)</span>
            @endif
        </label>
        @if ($counter)
            <span id="{{ $id }}-counter" class="mono-label" aria-live="off"></span>
        @endif
    </div>

    @if ($textarea)
        <textarea {{ $attributes->merge($shared)->class('input') }}>{{ $current }}</textarea>
    @else
        <input type="{{ $type }}" value="{{ $type === 'password' ? '' : $current }}" {{ $attributes->merge($shared)->class('input') }}>
    @endif

    @if ($hint)
        <p id="{{ $id }}-hint" class="field-hint">{{ $hint }}</p>
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="field-error">
            <x-icon name="alert" class="mt-0.5 size-4" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
