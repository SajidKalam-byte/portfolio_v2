<x-layouts.admin title="Skills">
    <x-slot:actions>
        <a href="{{ route('admin.skills.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="btn-icon" /> New skill</a>
    </x-slot:actions>

    <p class="max-w-2xl text-sm muted">
        Skills are grouped by the categories defined in <code class="font-mono">config/portfolio.php</code>. Only list tools you’ve used in real work — there are deliberately no proficiency levels.
    </p>

    <div class="mt-6 grid gap-6">
        @forelse (config('portfolio.skill_categories') as $key => $category)
            <section class="card overflow-x-auto" aria-labelledby="cat-{{ $key }}">
                <h2 id="cat-{{ $key }}" class="border-b border-line px-5 py-4 font-semibold">{{ $category['label'] }}</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Skill</th>
                            <th scope="col">Visible</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groups->get($key, collect()) as $skill)
                            <tr>
                                <td class="font-mono text-sm text-ink-subtle">{{ $skill->sort_order }}</td>
                                <td>
                                    <span class="font-medium">{{ $skill->name }}</span>
                                    @if ($skill->description)<p class="text-sm text-ink-subtle">{{ $skill->description }}</p>@endif
                                </td>
                                <td>
                                    <span @class(['badge', 'badge-success' => $skill->is_visible])>{{ $skill->is_visible ? 'Visible' : 'Hidden' }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.skills.edit', $skill) }}" class="icon-btn" aria-label="Edit {{ $skill->name }}"><x-icon name="pencil" /></a>
                                        <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" data-confirm="Remove “{{ $skill->name }}”?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="icon-btn text-danger" aria-label="Remove {{ $skill->name }}"><x-icon name="trash" /></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-sm muted">No skills in this category.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @empty
            <p class="muted">No skill categories configured.</p>
        @endforelse
    </div>
</x-layouts.admin>
