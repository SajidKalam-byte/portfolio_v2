<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProjectRequest extends FormRequest
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
        /** @var Project|null $project */
        $project = $this->route('project');
        $maxKb = config('portfolio.uploads.max_image_kb');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:191', 'alpha_dash:ascii', Rule::unique('projects', 'slug')->ignore($project)],
            'short_description' => ['required', 'string', 'max:300'],
            'description' => ['required', 'string', 'max:5000'],
            'long_description' => ['nullable', 'string', 'max:20000'],
            'category' => ['required', Rule::in(array_keys(config('portfolio.project_categories')))],
            'project_type' => ['required', Rule::in(array_keys(config('portfolio.project_types')))],
            'role' => ['required', 'string', 'max:255'],
            'client' => ['nullable', 'string', 'max:255'],
            'period' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', 'string', 'max:80'],
            'is_confidential' => ['boolean'],

            'image_path' => ['nullable', 'string', 'max:255', 'not_regex:/(\.\.|^\/|^[a-z]+:)/i'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'og_image_path' => ['nullable', 'string', 'max:255', 'not_regex:/(\.\.|^\/|^[a-z]+:)/i'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}", 'dimensions:max_width=4000,max_height=4000'],

            'tech_stack' => ['array', 'max:30'],
            'tech_stack.*' => ['string', 'max:60'],
            'highlights' => ['array', 'max:30'],
            'highlights.*' => ['string', 'max:300'],
            'responsibilities' => ['array', 'max:30'],
            'responsibilities.*' => ['string', 'max:500'],
            'architecture_json' => ['nullable', 'json', 'max:20000'],
            'metrics_json' => ['nullable', 'json', 'max:5000'],

            'project_url' => ['nullable', 'url:http,https', 'max:255'],
            'project_url_label' => ['nullable', 'string', 'max:60'],
            'github_url' => ['nullable', 'url:https', 'max:255'],
            'case_study_content' => ['nullable', 'string', 'max:100000'],

            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],

            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $architecture = $this->decoded('architecture_json');

                if ($architecture !== null && ! is_array($architecture['layers'] ?? null)) {
                    $validator->errors()->add('architecture_json', 'Architecture must be an object with a "layers" array.');
                }

                $metrics = $this->decoded('metrics_json');

                if ($metrics !== null && ! array_is_list($metrics)) {
                    $validator->errors()->add('metrics_json', 'Metrics must be a JSON array of {"value", "label", "note"} objects.');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('title'))),
            'tech_stack' => $this->splitList($this->input('tech_stack'), '/[\n,]+/'),
            'highlights' => $this->splitList($this->input('highlights'), '/\n+/'),
            'responsibilities' => $this->splitList($this->input('responsibilities'), '/\n+/'),
            'is_confidential' => $this->boolean('is_confidential'),
            'is_featured' => $this->boolean('is_featured'),
            'is_published' => $this->boolean('is_published'),
            'sort_order' => $this->input('sort_order', 0) ?? 0,
        ]);
    }

    /**
     * Attributes ready for Project::fill().
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->safe()->except(['cover', 'architecture_json', 'metrics_json']);

        $data['architecture'] = $this->decoded('architecture_json');
        $data['metrics'] = $this->decoded('metrics_json');

        foreach (['tech_stack', 'highlights', 'responsibilities'] as $list) {
            $data[$list] = $data[$list] ?: null;
        }

        return $data;
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

    private function decoded(string $key): ?array
    {
        $value = trim((string) $this->input($key));

        if ($value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
