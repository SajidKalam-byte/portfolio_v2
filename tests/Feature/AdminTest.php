<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.projects.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_pages_are_not_indexable(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_non_admin_users_cannot_sign_in(): void
    {
        $user = User::factory()->create(['password' => 'correct-horse-battery-9']);

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'correct-horse-battery-9'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_non_admins_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_sign_in_and_out(): void
    {
        $admin = User::factory()->create(['password' => 'correct-horse-battery-9'])
            ->forceFill(['is_admin' => true]);
        $admin->save();

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'correct-horse-battery-9'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Deployment checks');

        $this->post(route('admin.logout'))->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_is_throttled(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('admin.login.store'), ['email' => 'x@example.com', 'password' => 'wrong-password']);
        }

        $this->post(route('admin.login.store'), ['email' => 'x@example.com', 'password' => 'wrong-password'])
            ->assertStatus(429);
    }

    public function test_every_admin_screen_renders(): void
    {
        $this->seed(DatabaseSeeder::class);
        $message = ContactMessage::create(['name' => 'A', 'email' => 'a@example.com', 'message' => str_repeat('x', 30)]);
        $project = Project::first();

        $this->actingAs($this->admin());

        foreach ([
            route('admin.dashboard'), route('admin.projects.index'), route('admin.projects.create'), route('admin.projects.edit', $project),
            route('admin.skills.index'), route('admin.skills.create'), route('admin.experiences.index'), route('admin.experiences.create'),
            route('admin.educations.index'), route('admin.educations.create'), route('admin.messages.index'), route('admin.messages.show', $message),
            route('admin.settings.edit'),
        ] as $uri) {
            $this->get($uri)->assertOk();
        }

        $this->assertSame('read', $message->fresh()->status, 'Opening a message marks it as read.');
    }

    public function test_admin_can_create_a_project_with_structured_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [
                'title' => 'New Platform',
                'slug' => '',
                'short_description' => 'Short',
                'description' => 'Overview text',
                'category' => 'web-app',
                'project_type' => 'personal',
                'role' => 'Developer',
                'tech_stack' => 'Django, PostgreSQL',
                'highlights' => "One\nTwo",
                'architecture_json' => '{"layers": [{"title": "App"}]}',
                'sort_order' => 5,
                'is_published' => '1',
            ])
            ->assertRedirect();

        $project = Project::where('slug', 'new-platform')->sole();
        $this->assertSame(['Django', 'PostgreSQL'], $project->tech_stack);
        $this->assertSame(['One', 'Two'], $project->highlights);
        $this->assertSame('App', $project->architectureLayers()[0]['title']);
        $this->assertTrue($project->isPublic());
    }

    public function test_invalid_architecture_json_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.projects.create'))
            ->post(route('admin.projects.store'), [
                'title' => 'Broken', 'short_description' => 's', 'description' => 'd', 'category' => 'web-app',
                'project_type' => 'personal', 'role' => 'Dev', 'sort_order' => 1,
                'architecture_json' => '{"not_layers": true}',
            ])
            ->assertSessionHasErrors('architecture_json');
    }

    public function test_cover_uploads_reject_non_images(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.projects.update', $project), [
                ...$project->only(['title', 'slug', 'short_description', 'description', 'category', 'project_type', 'role', 'sort_order']),
                'cover' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
            ])
            ->assertSessionHasErrors('cover');
    }

    public function test_cover_upload_is_stored_in_public_uploads(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.projects.update', $project), [
                ...$project->only(['title', 'slug', 'short_description', 'description', 'category', 'project_type', 'role', 'sort_order']),
                // A real 1×1 PNG, so the test doesn't need the GD extension.
                'cover' => UploadedFile::fake()->createWithContent('cover.png', base64_decode(
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
                )),
            ])
            ->assertSessionHasNoErrors();

        $path = $project->fresh()->image_path;
        $this->assertStringStartsWith('uploads/projects/', $path);
        $this->assertStringEndsWith('.png', $path);
        $this->assertFileExists(public_path($path));

        File::delete(public_path($path));
    }

    public function test_settings_update_changes_public_contact_details(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), [
                'contact_email' => 'hello@example.com',
                'show_email' => '1',
                'phone' => '+91 98765 43210',
                'show_phone' => '0',
                'resume_path' => 'files/sajid-kalam-resume.pdf',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        auth()->logout();

        $this->get('/contact')
            ->assertSee('hello@example.com')
            ->assertDontSee('+91 98765 43210');
    }

    public function test_settings_reject_resume_paths_that_escape_the_public_folder(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), ['resume_path' => '../storage/secret.pdf'])
            ->assertSessionHasErrors('resume_path');
    }

    public function test_message_status_can_be_updated_and_messages_deleted(): void
    {
        $message = ContactMessage::create(['name' => 'A', 'email' => 'a@example.com', 'message' => str_repeat('x', 30)]);
        $this->actingAs($this->admin());

        $this->patch(route('admin.messages.update', $message), ['status' => 'replied']);
        $this->assertSame('replied', $message->fresh()->status);

        $this->delete(route('admin.messages.destroy', $message))->assertRedirect(route('admin.messages.index'));
        $this->assertModelMissing($message);
    }
}
