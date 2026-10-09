@php
    /** @var array<string, mixed> $settings */
    /** @var \App\Support\ResumeFile $resume */
@endphp

<x-layouts.admin title="Settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="grid gap-8">
        @csrf
        @method('PUT')

        <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="contact-settings">
            <h2 id="contact-settings" class="font-semibold">Public contact details</h2>
            <div class="grid gap-6 md:grid-cols-2">
                <x-form.input name="contact_email" type="email" label="Public email" :value="$settings['contact_email']" :optional="true"
                              hint="Only one address is ever shown publicly." />
                <x-form.input name="phone" label="Phone" :value="$settings['phone']" :optional="true" maxlength="30" />
            </div>
            <div class="flex flex-wrap gap-8">
                <x-form.checkbox name="show_email" label="Show email publicly" :checked="$settings['show_email']" />
                <x-form.checkbox name="show_phone" label="Show phone publicly" :checked="$settings['show_phone']" hint="Off by default." />
            </div>
            <x-form.input name="location" label="Location" :value="$settings['location']" :optional="true" maxlength="120" />
        </section>

        <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="status-settings">
            <h2 id="status-settings" class="font-semibold">Status line (home page)</h2>
            <x-form.input name="status_text" label="Current status" :value="$settings['status_text']" :optional="true" maxlength="120" />
            <x-form.checkbox name="open_to_work" label="Show “open to opportunities” note" :checked="$settings['open_to_work']" />
            <x-form.input name="open_to_work_text" label="Availability text" :value="$settings['open_to_work_text']" :optional="true" maxlength="120" />
        </section>

        <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="profile-settings">
            <h2 id="profile-settings" class="font-semibold">Profiles</h2>
            <div class="grid gap-6 md:grid-cols-2">
                <x-form.input name="github_url" type="url" label="GitHub URL" :value="$settings['github_url']" :optional="true" />
                <x-form.input name="linkedin_url" type="url" label="LinkedIn URL" :value="$settings['linkedin_url']" :optional="true" />
            </div>
            <x-form.checkbox name="schema_works_for" label="Include current employer in structured data (schema.org worksFor)"
                             :checked="$settings['schema_works_for']" hint="Enable once you’re happy for search engines to associate you with your employer." />
        </section>

        <section class="card grid gap-6 p-6 sm:p-8" aria-labelledby="resume-settings">
            <h2 id="resume-settings" class="font-semibold">Resume</h2>
            <p class="-mt-3 text-sm muted">
                @if ($resume->exists())
                    Current PDF: <code class="font-mono">{{ $resume->relativePath() }}</code> · {{ $resume->sizeLabel() }} · updated {{ $resume->updatedAt()?->toFormattedDateString() }}
                @else
                    No PDF found at <code class="font-mono">{{ $resume->relativePath() ?? '—' }}</code>. The download button stays hidden until one exists.
                @endif
            </p>
            <div class="grid gap-6 md:grid-cols-2">
                <div class="field">
                    <label for="field-resume-file" class="label">Upload PDF <span class="label-optional">(optional)</span></label>
                    <input id="field-resume-file" type="file" name="resume_file" accept="application/pdf" class="input" aria-describedby="field-resume-file-hint">
                    <p id="field-resume-file-hint" class="field-hint">Replaces the file at the path on the right. Max {{ config('portfolio.uploads.max_resume_kb') / 1024 }} MB.</p>
                </div>
                <x-form.input name="resume_path" label="PDF path" :value="$settings['resume_path']" :optional="true" hint="Relative to the public folder." />
                <x-form.input name="resume_external_url" type="url" label="External resume URL" :value="$settings['resume_external_url']" :optional="true"
                              hint="Used only when no local PDF exists." />
            </div>
        </section>

        <div>
            <button type="submit" class="btn btn-primary">Save settings</button>
        </div>
    </form>
</x-layouts.admin>
