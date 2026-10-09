@php $editing = $skill->exists; @endphp

<x-layouts.admin :title="$editing ? 'Edit skill' : 'New skill'" :heading="$editing ? $skill->name : 'New skill'">
    <form method="POST" action="{{ $editing ? route('admin.skills.update', $skill) : route('admin.skills.store') }}" class="card grid max-w-2xl gap-6 p-6 sm:p-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-form.input name="name" label="Name" :value="$skill->name" required maxlength="80" />
        <x-form.select name="category" label="Category" :value="$skill->category"
                       :options="collect(config('portfolio.skill_categories'))->map(fn ($c) => $c['label'])->all()" />
        <x-form.input name="description" label="Description" :value="$skill->description" :optional="true" maxlength="255"
                      hint="Shown as a tooltip. Keep it factual, e.g. where you used it." />
        <x-form.input name="sort_order" type="number" label="Sort order" :value="$skill->sort_order ?? 0" min="0" required />
        <x-form.checkbox name="is_visible" label="Visible on the public site" :checked="$skill->is_visible ?? true" />

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add skill' }}</button>
            <a href="{{ route('admin.skills.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
