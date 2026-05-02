<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeSuperAdmin extends Command
{
    protected $signature   = 'app:make-super-admin {email? : Email of the user to promote}';
    protected $description = 'Promote an existing user to super_admin, or create one if not found';

    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Enter the user email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            if (!$this->confirm("No user found with [{$email}]. Create a new super admin account?")) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }

            $name     = $this->ask('Full name');
            $password = $this->secret('Password (min 8 chars)');

            $user = User::create([
                'name'              => $name,
                'email'             => $email,
                'password'          => Hash::make($password),
                'role'              => User::ROLE_SUPER_ADMIN,
                'email_verified_at' => now(),
            ]);

            $this->info("✓ Super Admin created: {$user->name} <{$user->email}>");
            return self::SUCCESS;
        }

        $old = $user->roleBadge();
        $user->update(['role' => User::ROLE_SUPER_ADMIN]);
        $this->info("✓ {$user->name} <{$user->email}> promoted from {$old} → Super Admin");

        return self::SUCCESS;
    }
}
