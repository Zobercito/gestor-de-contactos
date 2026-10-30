<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make(config('demo.password', 'demo1234'));

        $users = [
            [
                'email' => config('demo.accounts.admin', 'admin@gestordecontactos.test'),
                'name' => 'Ana Torres',
                'role' => UserRole::Admin,
            ],
            [
                'email' => config('demo.accounts.editor', 'editor@gestordecontactos.test'),
                'name' => 'Elena Ruiz',
                'role' => UserRole::Editor,
            ],
            [
                'email' => config('demo.accounts.viewer', 'lector@gestordecontactos.test'),
                'name' => 'Lucas Vega',
                'role' => UserRole::Viewer,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
