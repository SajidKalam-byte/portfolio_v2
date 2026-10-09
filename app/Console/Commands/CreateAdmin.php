<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'portfolio:admin
                            {--email= : Administrator email address}
                            {--name= : Display name}';

    protected $description = 'Create an administrator account, or reset an existing one’s password';

    public function handle(): int
    {
        $email = strtolower(trim((string) ($this->option('email') ?: $this->ask('Email address'))));
        $existing = User::where('email', $email)->first();

        if ($existing && ! $this->confirm("{$email} already exists. Reset its password and grant admin access?", false)) {
            $this->warn('Nothing changed.');

            return self::FAILURE;
        }

        $name = $existing?->name ?? ($this->option('name') ?: $this->ask('Display name', 'Administrator'));
        $password = (string) $this->secret('Password (min. 12 characters, letters and numbers)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'email' => ['required', 'email:rfc', 'max:190'],
                'name' => ['required', 'string', 'max:120'],
                'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = $existing ?? new User(['email' => $email]);
        $user->fill(['name' => $name, 'password' => $password]);
        $user->forceFill(['is_admin' => true, 'email_verified_at' => now()])->save();

        $this->info(($existing ? 'Updated' : 'Created')." administrator {$email}.");
        $this->line('Sign in at: '.url(config('portfolio.admin.path').'/login'));

        return self::SUCCESS;
    }
}
