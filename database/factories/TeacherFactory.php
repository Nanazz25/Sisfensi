<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
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
            'nip' => $this->faker->unique()->numerify('##################'), // 18 digit NIP
            'nuptk' => $this->faker->unique()->numerify('################'), // 16 digit NUPTK
            'nik' => $this->faker->unique()->numerify('################'), // 16 digit NIK
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'tempat_lahir' => $this->faker->city(),
            'tanggal_lahir' => $this->faker->date('Y-m-d', '-25 years'),
        ];
    }
}
