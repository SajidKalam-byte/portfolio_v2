<?php

namespace App\Http\Requests;

use App\Models\Testimonial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The form a client fills in from a private testimonial request link.
 */
class TestimonialSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'author_name' => ['required', 'string', 'min:2', 'max:120'],
            'author_title' => ['nullable', 'string', 'max:120'],
            'author_company' => ['nullable', 'string', 'max:120'],
            'relationship' => ['required', Rule::in(array_keys(Testimonial::RELATIONSHIPS))],
            'quote' => ['required', 'string', 'min:30', 'max:1200'],
            'source_url' => ['nullable', 'url:https', 'max:255'],
            'show_name' => ['boolean'],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quote.required' => 'Please write a few words about working together.',
            'quote.min' => 'Could you add a little more detail? At least :min characters.',
            'consent.accepted' => 'Please confirm that your testimonial may be published.',
            'source_url.url' => 'Please use a full https:// link (for example your LinkedIn profile or recommendation).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($value) => is_string($value) ? trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '') : $value;

        $this->merge([
            'author_name' => $clean($this->input('author_name')),
            'author_title' => $clean($this->input('author_title')),
            'author_company' => $clean($this->input('author_company')),
            'quote' => is_string($this->input('quote')) ? trim(str_replace("\r\n", "\n", $this->input('quote'))) : $this->input('quote'),
            'show_name' => $this->boolean('show_name'),
        ]);
    }
}
