<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RombonganBelajar>
 */
class RombonganBelajarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_rombel' => $this->faker->randomElement(['X', 'XI', 'XII']) . ' ' . $this->faker->randomElement(['RPL', 'TKJ', 'MM']) . ' ' . $this->faker->randomDigitNotNull(),
            'jurusan' => $this->faker->randomElement(['Rekayasa Perangkat Lunak', 'Teknik Komputer Jaringan', 'Multimedia']),
            'tahun_ajar_id' => \App\Models\TahunAjar::factory(),
            'wali_kelas_id' => \App\Models\Teacher::factory(),
        ];
    }
}
