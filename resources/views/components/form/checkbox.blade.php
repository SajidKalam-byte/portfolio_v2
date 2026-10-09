@props(['name', 'label', 'checked' => false, 'hint' => null, 'id' => null])

@php
    $id ??= 'field-'.\Illuminate\Support\Str::slug($name);
    $isChecked = (bool) old($name, $checked);
@endphp

<div class="flex items-start gap-3">
    {{-- Hidden input ensures an unchecked box submits "0". --}}
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" class="checkbox mt-0.5" @checked($isChecked)
           @if ($hint) aria-describedby="{{ $id }}-hint" @endif {{ $attributes }}>
    <div>
        <label for="{{ $id }}" class="label">{{ $label }}</label>
        @if ($hint)
            <p id="{{ $id }}-hint" class="field-hint">{{ $hint }}</p>
        @endif
    </div>
</div>
