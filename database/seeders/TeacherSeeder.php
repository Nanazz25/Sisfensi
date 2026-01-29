<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat 5 Guru
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => 'Guru ' . $i,
                'email' => 'guru' . $i . '@sekolah.id',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'nip' => '19800101' . str_pad($i, 3, '0', STR_PAD_LEFT), // Example NIP
            ]);
        }
    }
}
