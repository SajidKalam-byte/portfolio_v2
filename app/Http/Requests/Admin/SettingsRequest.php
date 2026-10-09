<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    private const BOOLEANS = ['show_email', 'show_phone', 'open_to_work', 'schema_works_for'];

    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = config('portfolio.uploads.max_resume_kb');

        return [
            'contact_email' => ['nullable', 'email:rfc', 'max:190'],
            'show_email' => ['boolean'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[+\d\s().-]+$/'],
            'show_phone' => ['boolean'],
            'location' => ['nullable', 'string', 'max:120'],
            'status_text' => ['nullable', 'string', 'max:120'],
            'open_to_work' => ['boolean'],
            'open_to_work_text' => ['nullable', 'string', 'max:120'],
            'github_url' => ['nullable', 'url:https', 'max:255'],
            'linkedin_url' => ['nullable', 'url:https', 'max:255'],
            'resume_path' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\/.-]+\.pdf$/', 'not_regex:/(\.\.|^\/)/'],
            'resume_external_url' => ['nullable', 'url:https', 'max:255'],
            'resume_file' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', "max:{$maxKb}"],
            'schema_works_for' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'resume_path.regex' => 'Use a relative path ending in .pdf, e.g. files/sajid-kalam-resume.pdf.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect(self::BOOLEANS)->mapWithKeys(fn ($key) => [$key => $this->boolean($key)])->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return $this->safe()->except(['resume_file']);
    }
}
