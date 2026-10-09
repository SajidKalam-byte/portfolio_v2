<x-layouts.admin title="Experience">
    <x-slot:actions>
        <a href="{{ route('admin.experiences.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="btn-icon" /> New role</a>
    </x-slot:actions>

    <div class="card overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">Order</th>
                    <th scope="col">Role</th>
                    <th scope="col">Period</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($experiences as $experience)
                    <tr>
                        <td class="font-mono text-sm text-ink-subtle">{{ $experience->sort_order }}</td>
                        <td>
                            <a href="{{ route('admin.experiences.edit', $experience) }}" class="font-medium hover:text-accent-ink">{{ $experience->role }}</a>
                            <p class="text-sm text-ink-subtle">{{ $experience->company }}</p>
                        </td>
                        <td class="text-sm muted">{{ $experience->periodLabel() }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('admin.experiences.edit', $experience) }}" class="icon-btn" aria-label="Edit {{ $experience->role }}"><x-icon name="pencil" /></a>
                                <form method="POST" action="{{ route('admin.experiences.destroy', $experience) }}" data-confirm="Remove this role?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn text-danger" aria-label="Remove {{ $experience->role }}"><x-icon name="trash" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-10 text-center muted">No roles yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
