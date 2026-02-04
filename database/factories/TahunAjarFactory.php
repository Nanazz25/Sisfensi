<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TahunAjar>
 */
class TahunAjarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = $this->faker->year();
        return [
            'nama' => $year . '/' . ($year + 1),
            'semester' => $this->faker->randomElement(['Ganjil', 'Genap']),
            'tanggal_mulai' => $this->faker->date('Y-m-d'),
            'tanggal_selesai' => $this->faker->date('Y-m-d'),
            'is_active' => false,
        ];
    }
}
