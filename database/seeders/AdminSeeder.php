<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $userId = config('app.admin_seed_user_id');

        if ($userId) {
            User::where('id', $userId)->update([
                'role' => 'admin',
                'is_admin' => true,
            ]);

            return;
        }

        User::updateOrCreate(
            ['email' => 'admin@biina.com'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'is_admin' => true,
                'password' => Hash::make('password'),
            ]
        );
    }
}
