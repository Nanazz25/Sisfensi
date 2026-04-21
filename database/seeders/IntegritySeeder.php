<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IntegritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Point Rules
        \App\Models\PointRule::create([
            'rule_name' => 'Datang Sebelum Jam 06:45',
            'target_role' => 'siswa',
            'condition_operator' => '<',
            'condition_value' => '06:45:00',
            'point_modifier' => 5,
        ]);

        \App\Models\PointRule::create([
            'rule_name' => 'Terlambat Masuk',
            'target_role' => 'siswa',
            'trigger_type' => 'attendance',
            'condition_operator' => '>',
            'condition_value' => '07:30:00',
            'point_modifier' => -5,
        ]);

        // 2. Manual Rules (For Guru)
        \App\Models\PointRule::create([
            'rule_name' => 'Tugas Tepat Waktu',
            'target_role' => 'siswa',
            'trigger_type' => 'manual',
            'condition_operator' => 'MANUAL',
            'condition_value' => 'N/A',
            'point_modifier' => 10,
        ]);

        \App\Models\PointRule::create([
            'rule_name' => 'Berpakaian Tidak Rapi',
            'target_role' => 'siswa',
            'trigger_type' => 'manual',
            'condition_operator' => 'MANUAL',
            'condition_value' => 'N/A',
            'point_modifier' => -10,
        ]);

        \App\Models\PointRule::create([
            'rule_name' => 'Membantu Kebersihan',
            'target_role' => 'siswa',
            'trigger_type' => 'manual',
            'condition_operator' => 'MANUAL',
            'condition_value' => 'N/A',
            'point_modifier' => 5,
        ]);

        // 3. Flexibility Items
        \App\Models\FlexibilityItem::create([
            'item_name' => 'Token Bebas Terlambat 15 Menit',
            'description' => 'Memaafkan keterlambatan kamu jika masih di bawah 15 menit.',
            'point_cost' => 50,
        ]);

        \App\Models\FlexibilityItem::create([
            'item_name' => 'Token Izin Tanpa Surat',
            'description' => 'Gunakan saat keadaan darurat tanpa perlu upload surat.',
            'point_cost' => 150,
        ]);
    }
}
