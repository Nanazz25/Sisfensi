<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FaceLog>
 */
class FaceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'confidence' => 0.95,
            'result' => 'match',
            'image_path' => 'logs/image.jpg',
            'created_at' => now(),
        ];
    }
}
