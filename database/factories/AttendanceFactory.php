<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'anggota_rombel_id' => \App\Models\AnggotaRombel::factory(),
            'schedule_id' => \App\Models\Schedule::factory(),
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'jenis_absensi' => 'masuk',
            'status' => 'hadir',
            'metode' => 'manual',
        ];
    }
}
