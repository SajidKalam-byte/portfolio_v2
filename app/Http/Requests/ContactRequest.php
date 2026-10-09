<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class ContactRequest extends FormRequest
{
    /** Name of the visually hidden honeypot input. */
    public const HONEYPOT = 'company_website';

    /** Name of the encrypted "form rendered at" timestamp. */
    public const TIMESTAMP = 'form_token';

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
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please tell me your name.',
            'email.required' => 'I need an email address to reply to.',
            'email.email' => 'That email address doesn’t look quite right.',
            'message.required' => 'Please write a short message.',
            'message.min' => 'Could you add a little more detail? At least :min characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->clean($this->input('name')),
            'email' => $this->clean($this->input('email')),
            'subject' => $this->clean($this->input('subject')),
            'message' => is_string($this->input('message'))
                ? trim(str_replace("\r\n", "\n", $this->input('message')))
                : $this->input('message'),
        ]);
    }

    /**
     * Bots tend to fill every field and submit instantly. Either signal means
     * the submission is silently discarded.
     */
    public function looksAutomated(): bool
    {
        if (filled($this->input(self::HONEYPOT))) {
            return true;
        }

        try {
            $renderedAt = (int) Crypt::decryptString((string) $this->input(self::TIMESTAMP));
        } catch (DecryptException) {
            return true;
        }

        return now()->timestamp - $renderedAt < config('portfolio.contact.min_seconds', 3);
    }

    private function clean(mixed $value): mixed
    {
        // Collapse whitespace and strip control characters from single-line fields.
        return is_string($value)
            ? trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '')
            : $value;
    }
}
