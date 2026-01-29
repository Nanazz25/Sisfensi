<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolLocation>
 */
class SchoolLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_lokasi' => 'Sekolah Utama',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_maks' => 100,
        ];
    }
}
