@props(['value', 'label' => 'Text'])

{{-- Hidden until clipboard.js enables it, so it never appears as a dead control. --}}
<button type="button" {{ $attributes->class('btn btn-secondary btn-sm') }} data-copy="{{ $value }}" data-copy-label="{{ $label }}" hidden>
    <x-icon name="copy" class="btn-icon" />
    <span data-copy-text>Copy</span>
    <span class="sr-only">{{ strtolower($label) }}</span>
</button>
