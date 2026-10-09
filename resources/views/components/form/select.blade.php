@props(['name', 'label', 'options' => [], 'value' => null, 'hint' => null, 'id' => null])

@php
    $id ??= 'field-'.\Illuminate\Support\Str::slug($name);
    $error = $errors->first($name);
    $current = (string) old($name, $value);
@endphp

<div class="field">
    <label for="{{ $id }}" class="label">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->class('input') }}
            @if ($hint || $error) aria-describedby="{{ trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : '')) }}" @endif
            @if ($error) aria-invalid="true" @endif>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint)
        <p id="{{ $id }}-hint" class="field-hint">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="field-error"><x-icon name="alert" class="mt-0.5 size-4" /><span>{{ $error }}</span></p>
    @endif
</div>
