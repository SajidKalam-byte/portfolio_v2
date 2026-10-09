<x-layouts.admin title="Projects">
    <x-slot:actions>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="btn-icon" /> New project</a>
    </x-slot:actions>

    <div class="card overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">Order</th>
                    <th scope="col">Project</th>
                    <th scope="col">Category</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                    <tr>
                        <td class="font-mono text-sm text-ink-subtle">{{ $project->sort_order }}</td>
                        <td>
                            <a href="{{ route('admin.projects.edit', $project) }}" class="font-medium hover:text-accent-ink">{{ $project->title }}</a>
                            <p class="font-mono text-xs text-ink-subtle">/projects/{{ $project->slug }} · {{ $project->images_count }} {{ \Illuminate\Support\Str::plural('image', $project->images_count) }}</p>
                        </td>
                        <td class="text-sm muted">{{ $project->categoryLabel() }}</td>
                        <td>
                            <div class="flex flex-wrap gap-1.5">
                                @if ($project->isPublic())
                                    <span class="badge badge-success">Published</span>
                                @elseif ($project->is_published)
                                    <span class="badge">Scheduled</span>
                                @else
                                    <span class="badge">Draft</span>
                                @endif
                                @if ($project->is_featured)<span class="badge badge-accent">Featured</span>@endif
                                @if ($project->is_confidential)<span class="badge"><x-icon name="lock" class="size-3" /> NDA</span>@endif
                            </div>
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                @if ($project->isPublic())
                                    <a href="{{ $project->url() }}" class="icon-btn" target="_blank" rel="noopener" aria-label="View {{ $project->title }} on the site"><x-icon name="eye" /></a>
                                @endif
                                <a href="{{ route('admin.projects.edit', $project) }}" class="icon-btn" aria-label="Edit {{ $project->title }}"><x-icon name="pencil" /></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center muted">No projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
