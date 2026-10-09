<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_administrator(): void
    {
        $this->artisan('portfolio:admin', ['--email' => 'Owner@Example.com', '--name' => 'Owner'])
            ->expectsQuestion('Password (min. 12 characters, letters and numbers)', 'a-strong-passphrase-42')
            ->expectsQuestion('Confirm password', 'a-strong-passphrase-42')
            ->assertSuccessful();

        $user = User::where('email', 'owner@example.com')->sole();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(Hash::check('a-strong-passphrase-42', $user->password));
    }

    public function test_it_rejects_weak_passwords(): void
    {
        $this->artisan('portfolio:admin', ['--email' => 'owner@example.com', '--name' => 'Owner'])
            ->expectsQuestion('Password (min. 12 characters, letters and numbers)', 'short')
            ->expectsQuestion('Confirm password', 'short')
            ->assertFailed();

        $this->assertSame(0, User::count());
    }
}
