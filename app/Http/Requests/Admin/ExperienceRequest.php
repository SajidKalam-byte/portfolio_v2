<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:255'],
            'company_url' => ['nullable', 'url:http,https', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'is_current' => ['boolean'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_if:is_current,false'],
            'description' => ['required', 'string', 'max:5000'],
            'highlights_json' => ['nullable', 'json', 'max:20000'],
            'tech_stack' => ['array', 'max:40'],
            'tech_stack.*' => ['string', 'max:60'],
            'related_projects' => ['array', 'max:10'],
            'related_projects.*' => ['string', 'max:255', 'exists:projects,slug'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.required_if' => 'Add an end date, or tick “Current role”.',
            'related_projects.*.exists' => 'Unknown project slug: :input',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $highlights = $this->decodedHighlights();

                if ($highlights === null) {
                    return;
                }

                foreach ($highlights as $group) {
                    if (! is_string($group) && ! (is_array($group) && is_array($group['items'] ?? null))) {
                        $validator->errors()->add('highlights_json', 'Each highlight must be a string or {"title": "…", "items": ["…"]}.');

                        return;
                    }
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_current' => $this->boolean('is_current'),
            'tech_stack' => $this->splitList($this->input('tech_stack'), '/[\n,]+/'),
            'related_projects' => $this->splitList($this->input('related_projects'), '/[\n,\s]+/'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->safe()->except(['highlights_json']);
        $data['highlights'] = $this->decodedHighlights();
        $data['end_date'] = $data['is_current'] ? null : ($data['end_date'] ?? null);
        $data['tech_stack'] = $data['tech_stack'] ?: null;
        $data['related_projects'] = $data['related_projects'] ?: null;

        return $data;
    }

    private function decodedHighlights(): ?array
    {
        $raw = trim((string) $this->input('highlights_json'));
        $decoded = $raw === '' ? null : json_decode($raw, true);

        return is_array($decoded) && array_is_list($decoded) ? $decoded : null;
    }

    /**
     * @return list<string>
     */
    private function splitList(mixed $value, string $pattern): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        return array_values(array_filter(array_map('trim', preg_split($pattern, (string) $value) ?: [])));
    }
}
