<?php

namespace Tests\Feature;

use App\Mail\TestimonialReceived;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function submission(array $overrides = []): array
    {
        return array_merge([
            'author_name' => 'Asha Client',
            'author_title' => 'Founder',
            'author_company' => 'Acme Logistics',
            'relationship' => 'client',
            'quote' => 'Sajid planned and delivered our portal end to end and kept us informed throughout.',
            'show_name' => '1',
            'consent' => '1',
        ], $overrides);
    }

    // --- Public display -----------------------------------------------------

    public function test_home_hides_the_section_until_a_testimonial_is_published(): void
    {
        Testimonial::factory()->received()->create(['quote' => 'Pending words that must not show']);
        Testimonial::factory()->withoutConsent()->create(['quote' => 'Unconsented words that must not show']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('In their')
            ->assertDontSee('Pending words that must not show')
            ->assertDontSee('Unconsented words that must not show');
    }

    public function test_home_shows_published_testimonials_with_featured_first(): void
    {
        Testimonial::factory()->create(['author_name' => 'Second Person', 'quote' => 'Regular testimonial text here.']);
        Testimonial::factory()->featured()->create(['author_name' => 'First Person', 'quote' => 'Featured testimonial text here.']);

        $html = $this->get('/')->assertOk()->assertSee('In their')->getContent();

        $this->assertLessThan(strpos($html, 'Second Person'), strpos($html, 'First Person'));
    }

    public function test_hidden_names_show_role_and_company_only(): void
    {
        Testimonial::factory()->create([
            'author_name' => 'Private Person',
            'author_title' => 'CTO',
            'author_company' => 'Health Startup',
            'show_name' => false,
        ]);

        $this->get('/')->assertSee('CTO')->assertSee('Health Startup')->assertDontSee('Private Person');
    }

    public function test_project_testimonials_appear_on_the_case_study_and_featured_card(): void
    {
        $project = Project::factory()->featured()->create(['title' => 'Portal Project']);
        $other = Project::factory()->create();
        Testimonial::factory()->create(['project_id' => $project->id, 'quote' => 'The portal changed how our team works.']);

        $this->get($project->url())
            ->assertOk()
            ->assertSee('id="feedback"', false)
            ->assertSee('href="#feedback"', false)
            ->assertSee('The portal changed how our team works.');

        $this->get($other->url())->assertDontSee('The portal changed how our team works.');

        // Featured project card on the home page carries a short pull-quote.
        $this->get('/')->assertSee('card-quote', false);
    }

    public function test_quotes_are_escaped(): void
    {
        Testimonial::factory()->create(['quote' => '<script>alert(1)</script> Great work all round.']);

        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_testimonials_are_not_emitted_as_review_structured_data(): void
    {
        Testimonial::factory()->create();

        $this->get('/')->assertDontSee('"Review"', false)->assertDontSee('AggregateRating', false);
    }

    // --- Request links ------------------------------------------------------

    public function test_admin_creates_a_request_link_shown_once(): void
    {
        $project = Project::factory()->create(['title' => 'Portal Project']);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.testimonials.request'), ['author_name' => 'Asha Client', 'project_id' => $project->id])
            ->assertRedirect(route('admin.testimonials.index'));

        $link = session('request_link');
        $this->assertStringStartsWith(url('/feedback/'), $link['url']);

        $testimonial = Testimonial::sole();
        $this->assertSame('requested', $testimonial->status);
        $this->assertNotSame(basename($link['url']), $testimonial->request_token_hash, 'Only a hash of the token is stored.');

        $this->followRedirects($response)->assertSee($link['url'], false)->assertSee('Suggested message');
    }

    public function test_client_can_submit_once_and_consent_is_recorded(): void
    {
        Mail::fake();
        config(['portfolio.contact.notify_email' => 'owner@example.com', 'mail.default' => 'smtp']);

        [$testimonial, $token] = Testimonial::createRequest(null, 'Asha Client', null);

        $this->get("/feedback/{$token}")
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('value="Asha Client"', false);

        $this->post("/feedback/{$token}", $this->submission())->assertRedirect('/feedback/thanks');

        $testimonial->refresh();
        $this->assertSame('received', $testimonial->status);
        $this->assertSame('form', $testimonial->consent_source);
        $this->assertNotNull($testimonial->consent_at);
        $this->assertNull($testimonial->request_token_hash);
        $this->assertFalse($testimonial->isPublic(), 'Submissions wait for review.');

        Mail::assertSent(TestimonialReceived::class);

        // Single use.
        $this->get("/feedback/{$token}")->assertStatus(410);
        $this->post("/feedback/{$token}", $this->submission())->assertStatus(410);
    }

    public function test_consent_is_required_on_the_form(): void
    {
        [, $token] = Testimonial::createRequest(null, null, null);

        $this->from("/feedback/{$token}")
            ->post("/feedback/{$token}", $this->submission(['consent' => '0']))
            ->assertSessionHasErrors('consent');

        $this->assertSame('requested', Testimonial::sole()->status);
    }

    public function test_expired_and_unknown_links_are_rejected(): void
    {
        [$testimonial, $token] = Testimonial::createRequest(null, null, null);
        $testimonial->forceFill(['request_expires_at' => now()->subDay()])->save();

        $this->get("/feedback/{$token}")->assertStatus(410);
        $this->get('/feedback/'.str_repeat('a', 48))->assertStatus(410);
    }

    public function test_regenerating_a_link_invalidates_the_old_one(): void
    {
        [$testimonial, $oldToken] = Testimonial::createRequest(null, 'Asha', null);

        $this->actingAs($this->admin())->post(route('admin.testimonials.regenerate', $testimonial));
        $newUrl = session('request_link')['url'];

        $this->get("/feedback/{$oldToken}")->assertStatus(410);
        $this->get(parse_url($newUrl, PHP_URL_PATH))->assertOk();
    }

    // --- Admin publishing ---------------------------------------------------

    public function test_publishing_requires_recorded_consent(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.testimonials.store'), [
                'author_name' => 'Manual Entry',
                'quote' => 'Received by email.',
                'status' => 'published',
                'sort_order' => 0,
            ])
            ->assertSessionHasErrors('consent');

        $this->actingAs($this->admin())
            ->post(route('admin.testimonials.store'), [
                'author_name' => 'Manual Entry',
                'quote' => 'Received by email.',
                'status' => 'published',
                'sort_order' => 0,
                'consent' => '1',
            ])
            ->assertSessionHasNoErrors();

        $testimonial = Testimonial::sole();
        $this->assertTrue($testimonial->isPublic());
        $this->assertSame('manual', $testimonial->consent_source);
        $this->get('/')->assertSee('Received by email.');
    }

    public function test_admin_screens_render(): void
    {
        $testimonial = Testimonial::factory()->create();
        Testimonial::createRequest(null, 'Pending Person', 'Sent on LinkedIn');

        $this->actingAs($this->admin());

        $this->get(route('admin.testimonials.index'))->assertOk()->assertSee('Pending Person')->assertSee('Request a testimonial');
        $this->get(route('admin.testimonials.create'))->assertOk();
        $this->get(route('admin.testimonials.edit', $testimonial))->assertOk()->assertSee('Permission recorded');
    }
}
