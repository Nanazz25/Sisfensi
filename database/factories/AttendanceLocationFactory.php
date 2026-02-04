<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceLocation>
 */
class AttendanceLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_id' => \App\Models\Attendance::factory(),
            'school_location_id' => \App\Models\SchoolLocation::factory(),
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius' => 10,
            'lokasi_valid' => true,
        ];
    }
}
