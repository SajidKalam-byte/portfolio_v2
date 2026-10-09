<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Support\PublicUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectImageController extends Controller
{
    public function __construct(private PublicUploads $uploads) {}

    public function store(Request $request, Project $project): RedirectResponse
    {
        $maxKb = config('portfolio.uploads.max_image_kb');

        $validated = $request->validateWithBag('images', [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}", 'dimensions:max_width=4000,max_height=4000'],
            'alt' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $path = $this->uploads->store($request->file('image'), "projects/{$project->slug}", $project->slug.'-screen');
        [$width, $height] = $this->uploads->dimensions($path);

        $project->images()->create([
            'path' => $path,
            'alt' => $validated['alt'],
            'caption' => $validated['caption'] ?? null,
            'width' => $width,
            'height' => $height,
            'sort_order' => $validated['sort_order'] ?? ((int) $project->images()->max('sort_order') + 10),
        ]);

        return to_route('admin.projects.edit', $project)->withFragment('gallery')->with('status', 'Image added.');
    }

    public function destroy(Project $project, ProjectImage $image): RedirectResponse
    {
        $this->uploads->delete($image->path);
        $image->delete();

        return to_route('admin.projects.edit', $project)->withFragment('gallery')->with('status', 'Image removed.');
    }
}
