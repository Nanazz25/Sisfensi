<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rombongan_belajar_id' => \App\Models\RombonganBelajar::factory(),
            'subject_id' => \App\Models\Subject::factory(),
            'teacher_id' => \App\Models\Teacher::factory(),
            'hari' => $this->faker->randomElement(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']),
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:30:00',
        ];
    }
}
