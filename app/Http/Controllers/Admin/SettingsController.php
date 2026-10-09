<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Support\PublicUploads;
use App\Support\ResumeFile;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Settings $settings, ResumeFile $resume): View
    {
        return view('admin.settings', [
            'settings' => $settings->all(),
            'resume' => $resume,
        ]);
    }

    public function update(SettingsRequest $request, Settings $settings, PublicUploads $uploads): RedirectResponse
    {
        $values = $request->settings();
        $values['resume_path'] = $values['resume_path'] ?: config('portfolio.defaults.resume_path');

        if ($request->hasFile('resume_file')) {
            $uploads->storeAs($request->file('resume_file'), $values['resume_path']);
        }

        $settings->put($values);

        return to_route('admin.settings.edit')->with('status', 'Settings saved.');
    }
}
