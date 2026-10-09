@php
    /** @var \App\Models\Experience $experience */
    $editing = $experience->exists;
    $highlights = $experience->highlights
        ? json_encode($experience->highlights, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        : '';
@endphp

<x-layouts.admin :title="$editing ? 'Edit role' : 'New role'" :heading="$editing ? $experience->role.' · '.$experience->company : 'New role'">
    <form method="POST" action="{{ $editing ? route('admin.experiences.update', $experience) : route('admin.experiences.store') }}" class="card grid gap-6 p-6 sm:p-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid gap-6 md:grid-cols-2">
            <x-form.input name="role" label="Role / title" :value="$experience->role" required maxlength="255" />
            <x-form.input name="company" label="Company" :value="$experience->company" required maxlength="255" />
            <x-form.input name="company_url" type="url" label="Company website" :value="$experience->company_url" :optional="true" />
            <x-form.input name="employment_type" label="Employment type" :value="$experience->employment_type" :optional="true" maxlength="40" placeholder="e.g. Full-time, Internship" />
            <x-form.input name="location" label="Location" :value="$experience->location" :optional="true" maxlength="255" />
            <x-form.input name="sort_order" type="number" label="Sort order" :value="$experience->sort_order ?? 0" min="0" required />
            <x-form.input name="start_date" type="date" label="Start date" :value="$experience->start_date?->format('Y-m-d')" required />
            <x-form.input name="end_date" type="date" label="End date" :value="$experience->end_date?->format('Y-m-d')" :optional="true" />
        </div>
        <x-form.checkbox name="is_current" label="Current role" :checked="$experience->is_current" />

        <x-form.input name="description" label="Summary" :value="$experience->description" :textarea="true" rows="4" required maxlength="5000" />
        <x-form.input name="highlights_json" label="Responsibilities (JSON)" :value="old('highlights_json', $highlights)" :textarea="true" rows="14" :optional="true" class="font-mono text-sm"
                      hint='Groups of bullet points: [{"title": "Backend & APIs", "items": ["…", "…"]}]' />
        <x-form.input name="tech_stack" label="Technologies" :value="implode(', ', $experience->tech_stack ?? [])" :optional="true" hint="Comma-separated." />
        <x-form.input name="related_projects" label="Related project slugs" :value="implode(', ', $experience->related_projects ?? [])" :optional="true"
                      hint="Comma-separated slugs of published projects to link, e.g. emr-patient-intake-platform" />

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add role' }}</button>
            <a href="{{ route('admin.experiences.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
