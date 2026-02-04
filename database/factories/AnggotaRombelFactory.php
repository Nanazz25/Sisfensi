<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnggotaRombel>
 */
class AnggotaRombelFactory extends Factory
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
            'peserta_didik_id' => \App\Models\PesertaDidik::factory(),
            'created_at' => now(),
        ];
    }
}
