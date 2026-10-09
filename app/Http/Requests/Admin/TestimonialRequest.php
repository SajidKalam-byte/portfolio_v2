<?php

namespace App\Http\Requests\Admin;

use App\Models\Testimonial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TestimonialRequest extends FormRequest
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
        $publishing = $this->input('status') === 'published';

        return [
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'author_name' => [$publishing ? 'required' : 'nullable', 'string', 'max:120'],
            'author_title' => ['nullable', 'string', 'max:120'],
            'author_company' => ['nullable', 'string', 'max:120'],
            'relationship' => ['nullable', Rule::in(array_keys(Testimonial::RELATIONSHIPS))],
            'show_name' => ['boolean'],
            'quote' => [$publishing ? 'required' : 'nullable', 'string', 'max:2000'],
            'source_url' => ['nullable', 'url:https', 'max:255'],
            'status' => ['required', Rule::in(['received', 'published', 'archived'])],
            'is_featured' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'consent' => ['boolean'],
            'remove_avatar' => ['boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024', 'dimensions:max_width=2000,max_height=2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                /** @var Testimonial|null $testimonial */
                $testimonial = $this->route('testimonial');
                $hasConsent = $this->boolean('consent') || $testimonial?->hasConsent();

                if ($this->input('status') === 'published' && ! $hasConsent) {
                    $validator->errors()->add('consent', 'Confirm you have permission to publish this testimonial before publishing it.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'author_name.required' => 'A published testimonial needs the person’s name (you can still hide it publicly).',
            'quote.required' => 'A published testimonial needs a quote.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'show_name' => $this->boolean('show_name'),
            'is_featured' => $this->boolean('is_featured'),
            'consent' => $this->boolean('consent'),
            'remove_avatar' => $this->boolean('remove_avatar'),
            'sort_order' => $this->input('sort_order', 0) ?? 0,
            'project_id' => $this->input('project_id') ?: null,
            'quote' => is_string($this->input('quote')) ? trim(str_replace("\r\n", "\n", $this->input('quote'))) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->safe()->except(['consent', 'avatar', 'remove_avatar']);
    }
}
