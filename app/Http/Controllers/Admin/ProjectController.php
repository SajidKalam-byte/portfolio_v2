<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use App\Support\PublicUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private PublicUploads $uploads) {}

    public function index(): View
    {
        return view('admin.projects.index', [
            'projects' => Project::ordered()->withCount('images')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.form', [
            'project' => new Project([
                'sort_order' => (int) Project::max('sort_order') + 10,
                'category' => 'web-app',
                'project_type' => 'personal',
            ]),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = new Project($request->payload());
        $this->applyCover($request, $project);
        $project->save();

        return to_route('admin.projects.edit', $project)->with('status', 'Project created.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', [
            'project' => $project->load('images'),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->fill($request->payload());
        $this->applyCover($request, $project);
        $project->save();

        return to_route('admin.projects.edit', $project)->with('status', 'Project saved.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->load('images');

        foreach ($project->images as $image) {
            $this->uploads->delete($image->path);
        }

        $this->uploads->delete($project->image_path);
        $project->delete();

        return to_route('admin.projects.index')->with('status', "“{$project->title}” deleted.");
    }

    private function applyCover(ProjectRequest $request, Project $project): void
    {
        if (! $request->hasFile('cover')) {
            return;
        }

        $previous = $project->getOriginal('image_path');
        $project->image_path = $this->uploads->store($request->file('cover'), 'projects', $project->slug ?: $project->title);

        if ($previous !== $project->image_path) {
            $this->uploads->delete($previous);
        }
    }
}
