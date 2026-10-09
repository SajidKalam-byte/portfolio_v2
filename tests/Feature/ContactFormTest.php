<?php

namespace Tests\Feature;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders_form_with_csrf_and_spam_protection(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="'.ContactRequest::HONEYPOT.'"', false)
            ->assertSee('name="'.ContactRequest::TIMESTAMP.'"', false);
    }

    public function test_valid_message_is_stored_and_confirmed_accurately(): void
    {
        $this->post('/contact', $this->payload())
            ->assertRedirect('/contact')
            ->assertSessionHas('contact_success');

        $message = ContactMessage::sole();
        $this->assertSame('Priya Recruiter', $message->name);
        $this->assertSame('pending', $message->status);
        $this->assertSame(64, strlen($message->ip_hash));
        $this->assertNotSame('127.0.0.1', $message->ip_hash, 'Raw IPs must not be stored.');

        // The confirmation claims receipt, not that an email was sent.
        $this->assertStringContainsString('has been received', session('contact_success'));
        $this->assertStringNotContainsString('email was sent', session('contact_success'));
    }

    public function test_validation_errors_are_returned_and_input_is_kept(): void
    {
        $this->from('/contact')
            ->post('/contact', $this->payload(['email' => 'not-an-email', 'message' => 'Too short']))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors(['email', 'message'])
            ->assertSessionHasInput('name', 'Priya Recruiter');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_honeypot_submissions_are_silently_discarded(): void
    {
        $this->post('/contact', $this->payload([ContactRequest::HONEYPOT => 'https://spam.example']))
            ->assertRedirect('/contact')
            ->assertSessionHas('contact_success');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_instant_submissions_are_treated_as_bots(): void
    {
        $this->post('/contact', $this->payload([
            ContactRequest::TIMESTAMP => Crypt::encryptString((string) now()->timestamp),
        ]))->assertSessionHas('contact_success');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_tampered_timestamp_is_treated_as_bot(): void
    {
        $this->post('/contact', $this->payload([ContactRequest::TIMESTAMP => 'forged']));

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_senders_are_rate_limited(): void
    {
        config(['portfolio.contact.max_per_hour' => 2]);

        $this->post('/contact', $this->payload());
        $this->post('/contact', $this->payload());
        $this->from('/contact')->post('/contact', $this->payload())
            ->assertSessionHasErrors('form');

        $this->assertSame(2, ContactMessage::count());
    }

    public function test_owner_is_emailed_when_mail_is_configured(): void
    {
        Mail::fake();
        config(['portfolio.contact.notify_email' => 'owner@example.com', 'mail.default' => 'smtp']);

        $this->post('/contact', $this->payload());

        Mail::assertSent(ContactMessageReceived::class, fn ($mail) => $mail->hasTo('owner@example.com')
            && $mail->hasReplyTo('priya@example.com'));
    }

    public function test_no_email_is_attempted_when_mailer_is_log(): void
    {
        Mail::fake();
        config(['portfolio.contact.notify_email' => 'owner@example.com', 'mail.default' => 'log']);

        $this->post('/contact', $this->payload());

        Mail::assertNothingSent();
        $this->assertSame(1, ContactMessage::count());
    }

    public function test_submissions_without_csrf_token_are_rejected(): void
    {
        // Laravel skips CSRF verification when env is "testing"; switch it off for this test.
        $this->app['env'] = 'local';

        $this->post('/contact', $this->payload())->assertStatus(419);
        $this->assertSame(0, ContactMessage::count());
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Priya Recruiter',
            'email' => 'priya@example.com',
            'subject' => 'Backend engineer role',
            'message' => 'Hi Sajid — we have a backend role on a Django team and would love to talk.',
            ContactRequest::TIMESTAMP => Crypt::encryptString((string) now()->subMinute()->timestamp),
            ContactRequest::HONEYPOT => '',
        ], $overrides);
    }
}
