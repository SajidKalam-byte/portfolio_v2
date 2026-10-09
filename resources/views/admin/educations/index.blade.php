<x-layouts.admin title="Education">
    <x-slot:actions>
        <a href="{{ route('admin.educations.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="btn-icon" /> New entry</a>
    </x-slot:actions>

    <div class="card overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">Order</th>
                    <th scope="col">Programme</th>
                    <th scope="col">Period</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($educations as $education)
                    <tr>
                        <td class="font-mono text-sm text-ink-subtle">{{ $education->sort_order }}</td>
                        <td>
                            <a href="{{ route('admin.educations.edit', $education) }}" class="font-medium hover:text-accent-ink">{{ $education->degree }}</a>
                            <p class="text-sm text-ink-subtle">{{ $education->institution }}</p>
                        </td>
                        <td class="text-sm muted">{{ $education->periodLabel() ?? $education->status }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('admin.educations.edit', $education) }}" class="icon-btn" aria-label="Edit {{ $education->degree }}"><x-icon name="pencil" /></a>
                                <form method="POST" action="{{ route('admin.educations.destroy', $education) }}" data-confirm="Remove this entry?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn text-danger" aria-label="Remove {{ $education->degree }}"><x-icon name="trash" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-10 text-center muted">No education entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
