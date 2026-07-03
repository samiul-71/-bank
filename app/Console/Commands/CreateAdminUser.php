<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Usage:
 *   php artisan admin:create
 *   php artisan admin:create --name="Admin" --email=admin@bank.test --password=secret12
 */
#[Signature('admin:create {--name=} {--email=} {--password=}')]
#[Description('Create (or promote) an admin user who can log in and view statements')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        $name     = $this->option('name')     ?: $this->ask('Name');
        $email    = $this->option('email')    ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password (min 8 chars)');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // Idempotent: update the existing user with this email, otherwise create.
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
            ]
        );

        $this->info(($user->wasRecentlyCreated ? 'Created' : 'Updated')
            . " admin: {$user->name} <{$user->email}>");

        return self::SUCCESS;
    }
}
