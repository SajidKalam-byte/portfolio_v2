@php
    /** @var \App\Models\Education $education */
    $editing = $education->exists;
@endphp

<x-layouts.admin :title="$editing ? 'Edit education' : 'New education'" :heading="$editing ? $education->degree : 'New education'">
    <form method="POST" action="{{ $editing ? route('admin.educations.update', $education) : route('admin.educations.store') }}" class="card grid gap-6 p-6 sm:p-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid gap-6 md:grid-cols-2">
            <x-form.input name="degree" label="Degree" :value="$education->degree" required maxlength="255" />
            <x-form.input name="institution" label="Institution" :value="$education->institution" required maxlength="255" />
            <x-form.input name="field_of_study" label="Field of study" :value="$education->field_of_study" :optional="true" maxlength="255" />
            <x-form.input name="location" label="Location" :value="$education->location" :optional="true" maxlength="255" />
            <x-form.input name="start_date" type="date" label="Start date" :value="$education->start_date?->format('Y-m-d')" :optional="true" hint="Only the year is shown publicly." />
            <x-form.input name="end_date" type="date" label="End date" :value="$education->end_date?->format('Y-m-d')" :optional="true" hint="Leave empty until confirmed." />
            <x-form.input name="status" label="Status" :value="$education->status" :optional="true" maxlength="40" placeholder="e.g. Completed, Pursuing" />
            <x-form.input name="grade" label="Grade" :value="$education->grade" :optional="true" maxlength="60" placeholder="e.g. CGPA 9.31/10" />
            <x-form.input name="sort_order" type="number" label="Sort order" :value="$education->sort_order ?? 0" min="0" required />
        </div>
        <x-form.input name="description" label="Description" :value="$education->description" :textarea="true" rows="3" :optional="true" maxlength="2000" />

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add entry' }}</button>
            <a href="{{ route('admin.educations.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
