<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExperienceRequest;
use App\Models\Experience;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    public function index(): View
    {
        return view('admin.experiences.index', [
            'experiences' => Experience::ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.experiences.form', [
            'experience' => new Experience(['sort_order' => 10, 'is_current' => false]),
        ]);
    }

    public function store(ExperienceRequest $request): RedirectResponse
    {
        Experience::create($request->payload());

        return to_route('admin.experiences.index')->with('status', 'Experience added.');
    }

    public function edit(Experience $experience): View
    {
        return view('admin.experiences.form', ['experience' => $experience]);
    }

    public function update(ExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $experience->update($request->payload());

        return to_route('admin.experiences.index')->with('status', 'Experience saved.');
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $experience->delete();

        return to_route('admin.experiences.index')->with('status', 'Experience removed.');
    }
}
