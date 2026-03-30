<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'fernandocardonatoro@gmail.com'],
            [
                'name' => 'Fernando Cardona Toro',
                'password' => Hash::make('12345678'),
                'role' => 'SuperAdmin',
                'email_verified_at' => now(),
            ]
        );
    }
}
