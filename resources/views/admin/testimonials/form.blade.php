@php
    /** @var \App\Models\Testimonial $testimonial */
    $editing = $testimonial->exists;
    $statusOptions = collect(\App\Models\Testimonial::STATUSES)->except('requested')->all();
@endphp

<x-layouts.admin :title="$editing ? 'Edit testimonial' : 'New testimonial'" :heading="$editing ? ($testimonial->author_name ?: 'Pending request') : 'Add a testimonial'">
    <x-slot:actions>
        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost btn-sm"><x-icon name="arrow-left" class="btn-icon" /> All testimonials</a>
    </x-slot:actions>

    @if ($editing && $testimonial->status === 'requested')
        <div class="alert alert-info mb-6">
            <x-icon name="info" class="size-5" />
            <p>This is an open request{{ $testimonial->isRequestOpen() ? ' (link expires '.$testimonial->request_expires_at->diffForHumans().')' : ' whose link has expired' }}. Saving this form closes the link and treats the entry as received.</p>
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <form method="POST" enctype="multipart/form-data"
              action="{{ $editing ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}"
              class="grid content-start gap-8">
            @csrf
            @if ($editing) @method('PUT') @endif

            <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="who">
                <h2 id="who" class="font-semibold">Who said it</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-form.input name="author_name" label="Name" :value="$testimonial->author_name" maxlength="120" />
                    <x-form.select name="relationship" label="Relationship" :value="$testimonial->relationship" :options="['' => '—'] + \App\Models\Testimonial::RELATIONSHIPS" />
                    <x-form.input name="author_title" label="Role / title" :value="$testimonial->author_title" :optional="true" maxlength="120" />
                    <x-form.input name="author_company" label="Company" :value="$testimonial->author_company" :optional="true" maxlength="120" />
                </div>
                <x-form.checkbox name="show_name" label="Show their name publicly" :checked="$testimonial->show_name ?? true"
                                 hint="When off, only role and company are shown — and no photo." />
                <x-form.input name="source_url" type="url" label="Link to the original" :value="$testimonial->source_url" :optional="true"
                              hint="e.g. their LinkedIn recommendation — lets visitors verify it." />
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="field">
                        <label for="field-avatar" class="label">Photo <span class="label-optional">(optional)</span></label>
                        <input id="field-avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="input" aria-describedby="field-avatar-hint">
                        <p id="field-avatar-hint" class="field-hint">Square JPG/PNG/WebP, max 1 MB. Only with their permission; initials are used otherwise.</p>
                    </div>
                    @if ($testimonial->avatar_path)
                        <div class="flex items-center gap-4">
                            <img src="{{ asset($testimonial->avatar_path) }}" alt="Current photo" class="avatar size-16">
                            <x-form.checkbox name="remove_avatar" label="Remove photo" />
                        </div>
                    @endif
                </div>
            </section>

            <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="what">
                <h2 id="what" class="font-semibold">What they said</h2>
                <x-form.input name="quote" label="Testimonial" :value="$testimonial->quote" :textarea="true" :counter="true" rows="8" maxlength="2000"
                              hint="Their words. Fix typos only — don’t change meaning. Blank line = new paragraph." />
                <x-form.select name="project_id" label="About which project?" :value="(string) $testimonial->project_id" :options="$projects"
                               hint="Linked testimonials also appear on that case study." />
            </section>

            <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="publishing">
                <h2 id="publishing" class="font-semibold">Permission & publishing</h2>

                @if ($testimonial->hasConsent())
                    <p class="flex items-start gap-2 text-sm">
                        <x-icon name="check-circle" class="mt-0.5 size-4 text-success" />
                        <span>
                            Permission recorded {{ $testimonial->consent_at->toFormattedDateString() }}
                            ({{ $testimonial->consent_source === 'form' ? 'given by them on the feedback form' : 'confirmed by you' }}).
                        </span>
                    </p>
                @else
                    <x-form.checkbox name="consent" label="I have this person’s permission to publish this testimonial"
                                     hint="Required before publishing — e.g. they agreed by email, or it’s their public LinkedIn recommendation." />
                    @error('consent')
                        <p class="field-error"><x-icon name="alert" class="mt-0.5 size-4" /><span>{{ $message }}</span></p>
                    @enderror
                @endif

                <div class="grid gap-6 md:grid-cols-2">
                    <x-form.select name="status" label="Status" :value="$testimonial->status === 'requested' ? 'received' : $testimonial->status" :options="$statusOptions" />
                    <x-form.input name="sort_order" type="number" label="Sort order" :value="$testimonial->sort_order ?? 0" min="0" required hint="Lower numbers first." />
                </div>
                <x-form.checkbox name="is_featured" label="Featured" :checked="$testimonial->is_featured" hint="Featured testimonials appear first on the home page." />
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Save testimonial' }}</button>
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>

        <aside class="grid content-start gap-4">
            @if ($testimonial->quote && $testimonial->author_name)
                <p class="eyebrow">Preview</p>
                <x-testimonial-card :testimonial="$testimonial" size="small" :show-project="false" />
            @endif

            @if ($editing)
                <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" data-confirm="Delete this testimonial permanently?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash" class="btn-icon" /> Delete testimonial</button>
                </form>
            @endif
        </aside>
    </div>
</x-layouts.admin>
