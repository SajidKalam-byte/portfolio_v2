<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EducationRequest;
use App\Models\Education;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EducationController extends Controller
{
    public function index(): View
    {
        return view('admin.educations.index', [
            'educations' => Education::ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.educations.form', [
            'education' => new Education(['sort_order' => 10]),
        ]);
    }

    public function store(EducationRequest $request): RedirectResponse
    {
        Education::create($request->validated());

        return to_route('admin.educations.index')->with('status', 'Education added.');
    }

    public function edit(Education $education): View
    {
        return view('admin.educations.form', ['education' => $education]);
    }

    public function update(EducationRequest $request, Education $education): RedirectResponse
    {
        $education->update($request->validated());

        return to_route('admin.educations.index')->with('status', 'Education saved.');
    }

    public function destroy(Education $education): RedirectResponse
    {
        $education->delete();

        return to_route('admin.educations.index')->with('status', 'Education removed.');
    }
}
