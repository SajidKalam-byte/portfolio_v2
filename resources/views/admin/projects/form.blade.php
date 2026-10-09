@php
    /** @var \App\Models\Project $project */
    $editing = $project->exists;
    $json = fn ($value) => $value ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
@endphp

<x-layouts.admin :title="$editing ? 'Edit project' : 'New project'" :heading="$editing ? $project->title : 'New project'">
    @if ($editing && $project->isPublic())
        <x-slot:actions>
            <a href="{{ $project->url() }}" class="btn btn-secondary btn-sm" target="_blank" rel="noopener"><x-icon name="eye" class="btn-icon" /> View on site</a>
        </x-slot:actions>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.projects.update', $project) : route('admin.projects.store') }}"
          class="grid gap-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="basics">
            <h2 id="basics" class="font-semibold">Basics</h2>
            <div class="grid gap-6 md:grid-cols-2">
                <x-form.input name="title" label="Title" :value="$project->title" required maxlength="255" />
                <x-form.input name="slug" label="URL slug" :value="$project->slug" maxlength="255" hint="Leave empty to generate from the title." />
            </div>
            <x-form.input name="short_description" label="Short description" :value="$project->short_description" :textarea="true" rows="2" required maxlength="300"
                          hint="Card and meta-description text. Max 300 characters." />
            <x-form.input name="description" label="Overview" :value="$project->description" :textarea="true" rows="5" required maxlength="5000"
                          hint="Opening section of the case study. Separate paragraphs with a blank line." />
            <x-form.input name="long_description" label="Additional overview" :value="$project->long_description" :textarea="true" rows="3" :optional="true" />
        </section>

        <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="context">
            <h2 id="context" class="font-semibold">Context</h2>
            <div class="grid gap-6 md:grid-cols-2">
                <x-form.select name="category" label="Category" :options="config('portfolio.project_categories')" :value="$project->category" />
                <x-form.select name="project_type" label="Type" :options="config('portfolio.project_types')" :value="$project->project_type" />
                <x-form.input name="role" label="My role" :value="$project->role" required maxlength="255" />
                <x-form.input name="client" label="Client" :value="$project->client" :optional="true" maxlength="255" hint="Only if you have permission to name them." />
                <x-form.input name="period" label="Timeline" :value="$project->period" :optional="true" maxlength="60" placeholder="e.g. Mar 2025 — Present" />
                <x-form.input name="status" label="Status label" :value="$project->status" :optional="true" maxlength="80" placeholder="e.g. Completed and deployed" />
            </div>
            <x-form.input name="tech_stack" label="Technology stack" :value="implode(', ', $project->tech_stack ?? [])" hint="Comma-separated." />
            <x-form.checkbox name="is_confidential" label="Confidential / NDA" :checked="$project->is_confidential"
                             hint="Hides external links and shows a confidentiality note." />
        </section>

        <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="case-study">
            <h2 id="case-study" class="font-semibold">Case study</h2>
            <x-form.input name="responsibilities" label="My contributions" :value="implode(PHP_EOL, $project->responsibilities ?? [])" :textarea="true" rows="6" :optional="true" hint="One per line." />
            <x-form.input name="highlights" label="At a glance" :value="implode(PHP_EOL, $project->highlights ?? [])" :textarea="true" rows="5" :optional="true" hint="Short feature/technique list, one per line. Place with [[highlights]] in the content." />
            <x-form.input name="case_study_content" label="Content (Markdown)" :value="$project->case_study_content" :textarea="true" rows="22" :optional="true" class="font-mono text-sm"
                          hint="Use ## for sections (they form the table of contents). Put [[architecture]] or [[highlights]] on their own line to place those blocks. Raw HTML is escaped." />
            <x-form.input name="architecture_json" label="Architecture (JSON)" :value="old('architecture_json', $json($project->architecture))" :textarea="true" rows="10" :optional="true" class="font-mono text-sm"
                          hint='{"heading": "Architecture overview", "intro": "…", "layers": [{"layer": "Data", "title": "PostgreSQL", "description": "…", "items": ["RDS"]}]}' />
            <x-form.input name="metrics_json" label="Verified metrics (JSON)" :value="old('metrics_json', $json($project->metrics))" :textarea="true" rows="4" :optional="true" class="font-mono text-sm"
                          hint='Only verified figures: [{"value": "98%", "label": "Reported accuracy", "note": "…"}]' />
        </section>

        <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="media">
            <h2 id="media" class="font-semibold">Media & links</h2>
            <div class="grid gap-6 md:grid-cols-2">
                <div class="field">
                    <label for="field-cover" class="label">Upload cover image <span class="label-optional">(optional)</span></label>
                    <input id="field-cover" type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="input" aria-describedby="field-cover-hint">
                    <p id="field-cover-hint" class="field-hint">JPG, PNG or WebP, max {{ config('portfolio.uploads.max_image_kb') / 1024 }} MB. 16:10 works best (e.g. 1600×1000).</p>
                </div>
                <x-form.input name="image_path" label="…or cover image path" :value="$project->image_path" :optional="true" hint="Relative to the public folder, e.g. images/projects/emr/cover.svg" />
                <x-form.input name="image_alt" label="Cover alt text" :value="$project->image_alt" :optional="true" maxlength="255" />
                <x-form.input name="og_image_path" label="Open Graph image path" :value="$project->og_image_path" :optional="true" hint="1200×630 PNG/JPG, e.g. images/og/emr.png" />
                <x-form.input name="project_url" type="url" label="Project URL" :value="$project->project_url" :optional="true" />
                <x-form.input name="project_url_label" label="Project link label" :value="$project->project_url_label" :optional="true" maxlength="60" placeholder="Visit project" />
                <x-form.input name="github_url" type="url" label="GitHub URL" :value="$project->github_url" :optional="true" />
            </div>
            @if ($project->coverUrl())
                <figure class="max-w-sm">
                    <img src="{{ $project->coverUrl() }}" alt="Current cover" class="rounded-xl border border-line">
                    <figcaption class="mt-2 text-xs text-ink-subtle">Current cover</figcaption>
                </figure>
            @endif
        </section>

        <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="publishing">
            <h2 id="publishing" class="font-semibold">SEO & publishing</h2>
            <div class="grid gap-6 md:grid-cols-2">
                <x-form.input name="seo_title" label="SEO title" :value="$project->seo_title" :optional="true" maxlength="255" hint="Defaults to “Title — Case study”." />
                <x-form.input name="seo_description" label="SEO description" :value="$project->seo_description" :optional="true" maxlength="320" hint="Defaults to the short description." />
                <x-form.input name="sort_order" type="number" label="Sort order" :value="$project->sort_order ?? 0" min="0" required hint="Lower numbers appear first." />
                <x-form.input name="published_at" type="datetime-local" label="Publish at" :value="$project->published_at?->format('Y-m-d\TH:i')" :optional="true" hint="Leave empty to publish immediately when “Published” is ticked." />
            </div>
            <div class="flex flex-wrap gap-8">
                <x-form.checkbox name="is_published" label="Published" :checked="$project->is_published" />
                <x-form.checkbox name="is_featured" label="Featured" :checked="$project->is_featured" hint="Featured projects get the large cards on the home page." />
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Create project' }}</button>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

    @if ($editing)
        <section id="gallery" class="card mt-10 grid gap-6 p-6 sm:p-8" aria-labelledby="gallery-title">
            <h2 id="gallery-title" class="font-semibold">Gallery images</h2>
            <p class="-mt-4 text-sm text-ink-subtle">Only upload screenshots you’re allowed to publish — never patient, client, or credential data.</p>

            @if ($project->images->isNotEmpty())
                <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($project->images as $image)
                        <li class="card overflow-hidden">
                            <img src="{{ $image->url() }}" alt="{{ $image->alt }}" class="aspect-video w-full object-cover" loading="lazy">
                            <div class="grid gap-3 p-4 text-sm">
                                <p class="muted">{{ $image->alt }}</p>
                                <form method="POST" action="{{ route('admin.projects.images.destroy', [$project, $image]) }}" data-confirm="Remove this image?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash" class="btn-icon" /> Remove</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('admin.projects.images.store', $project) }}" enctype="multipart/form-data" class="grid gap-5 border-t border-line pt-6 md:grid-cols-2">
                @csrf
                <div class="field">
                    <label for="gallery-image" class="label">Image</label>
                    <input id="gallery-image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="input" required>
                    @error('image', 'images')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <x-form.input name="alt" label="Alt text" bag="images" required maxlength="255" id="gallery-alt" />
                <x-form.input name="caption" label="Caption" bag="images" :optional="true" maxlength="255" id="gallery-caption" />
                <div class="flex items-end">
                    <button type="submit" class="btn btn-secondary"><x-icon name="plus" class="btn-icon" /> Add image</button>
                </div>
            </form>
        </section>

        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" class="mt-10" data-confirm="Delete “{{ $project->title }}” and its uploaded images? This can’t be undone.">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash" class="btn-icon" /> Delete project</button>
        </form>
    @endif
</x-layouts.admin>
