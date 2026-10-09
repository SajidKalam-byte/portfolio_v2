<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Project;
use App\Models\Testimonial;
use App\Support\PublicUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __construct(private PublicUploads $uploads) {}

    public function index(): View
    {
        $testimonials = Testimonial::with('project')
            ->orderByRaw("case status when 'received' then 0 when 'requested' then 1 when 'published' then 2 else 3 end")
            ->ordered()
            ->get();

        return view('admin.testimonials.index', [
            'testimonials' => $testimonials,
            'projects' => $this->projectOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.testimonials.form', [
            'testimonial' => new Testimonial(['status' => 'received', 'show_name' => true, 'relationship' => 'client', 'sort_order' => 0]),
            'projects' => $this->projectOptions(),
        ]);
    }

    public function store(TestimonialRequest $request): RedirectResponse
    {
        $testimonial = new Testimonial($request->payload());
        $this->applyConsent($request, $testimonial);
        $this->applyAvatar($request, $testimonial);
        $testimonial->submitted_at ??= now();
        $testimonial->save();

        return to_route('admin.testimonials.edit', $testimonial)->with('status', 'Testimonial saved.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.form', [
            'testimonial' => $testimonial,
            'projects' => $this->projectOptions(),
        ]);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->fill($request->payload());
        $this->applyConsent($request, $testimonial);
        $this->applyAvatar($request, $testimonial);

        // Editing an open request by hand closes its link.
        if ($testimonial->getOriginal('status') === 'requested') {
            $testimonial->forceFill(['request_token_hash' => null, 'request_expires_at' => null, 'submitted_at' => now()]);
        }

        $testimonial->save();

        return to_route('admin.testimonials.edit', $testimonial)->with('status', 'Testimonial saved.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->uploads->delete($testimonial->avatar_path);
        $testimonial->delete();

        return to_route('admin.testimonials.index')->with('status', 'Testimonial deleted.');
    }

    /**
     * Create a private request link to send to a client.
     */
    public function requestLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'author_name' => ['nullable', 'string', 'max:120'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'request_note' => ['nullable', 'string', 'max:255'],
        ]);

        [$testimonial, $token] = Testimonial::createRequest(
            $validated['project_id'] ?? null,
            $validated['author_name'] ?? null,
            $validated['request_note'] ?? null,
        );
        $testimonial->load('project');

        return to_route('admin.testimonials.index')->with('request_link', [
            'id' => $testimonial->id,
            'url' => route('testimonial.request', $token),
            'name' => $testimonial->author_name,
            'project' => $testimonial->project?->title,
        ]);
    }

    /**
     * Issue a new link for a request (e.g. the old one expired or was lost). The previous link stops working.
     */
    public function regenerateLink(Testimonial $testimonial): RedirectResponse
    {
        abort_unless($testimonial->status === 'requested', 404);

        $token = $testimonial->regenerateRequestToken();
        $testimonial->loadMissing('project');

        return to_route('admin.testimonials.index')->with('request_link', [
            'id' => $testimonial->id,
            'url' => route('testimonial.request', $token),
            'name' => $testimonial->author_name,
            'project' => $testimonial->project?->title,
        ]);
    }

    private function applyConsent(TestimonialRequest $request, Testimonial $testimonial): void
    {
        if ($request->boolean('consent') && ! $testimonial->hasConsent()) {
            $testimonial->forceFill(['consent_at' => now(), 'consent_source' => 'manual']);
        }
    }

    private function applyAvatar(TestimonialRequest $request, Testimonial $testimonial): void
    {
        if ($request->boolean('remove_avatar')) {
            $this->uploads->delete($testimonial->avatar_path);
            $testimonial->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            $previous = $testimonial->avatar_path;
            $testimonial->avatar_path = $this->uploads->store($request->file('avatar'), 'testimonials', $testimonial->author_name ?: 'avatar');
            $this->uploads->delete($previous);
        }
    }

    /**
     * @return array<string, string>
     */
    private function projectOptions(): array
    {
        return ['' => '— General (not about one project) —'] + Project::ordered()->pluck('title', 'id')->all();
    }
}
