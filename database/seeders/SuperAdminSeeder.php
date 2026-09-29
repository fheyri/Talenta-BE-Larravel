<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL');
        $password = env('SUPERADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('SUPERADMIN_EMAIL / SUPERADMIN_PASSWORD belum diisi di .env, super admin tidak dibuat.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SUPERADMIN_NAME', 'Super Admin'),
                'password' => $password,
                'role' => Role::SuperAdmin,
                'is_active' => true,
                'is_profile_complete' => true,
            ]
        );
    }
}
