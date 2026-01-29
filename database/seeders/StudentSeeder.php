<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PesertaDidik;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat 30 Siswa
        for ($i = 1; $i <= 30; $i++) {
            $user = User::create([
                'name' => 'Siswa ' . $i,
                'email' => 'siswa' . $i . '@sekolah.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            PesertaDidik::create([
                'user_id' => $user->id,
                'nis' => '2023' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'foto_wajah' => 'students/default.png',
                'face_embedding' => json_encode(array_fill(0, 128, 0.1)),
            ]);
        }
    }
}
