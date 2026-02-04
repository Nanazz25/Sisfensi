<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PesertaDidik>
 */
class PesertaDidikFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'nama_lengkap' => $this->faker->name(),
            'no_induk' => $this->faker->unique()->numerify('########'),
            'nisn' => $this->faker->unique()->numerify('##########'),
            'nik' => $this->faker->unique()->numerify('################'),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'tempat_lahir' => $this->faker->city(),
            'tanggal_lahir' => $this->faker->date('Y-m-d', '-15 years'),
            'foto_wajah' => 'default.png',
            'face_embedding' => json_encode(array_fill(0, 128, 0.1)),
        ];
    }
}
