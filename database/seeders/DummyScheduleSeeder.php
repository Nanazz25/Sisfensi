<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\RombonganBelajar;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class DummyScheduleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('schedules')->delete();

        $rombels = RombonganBelajar::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        if ($subjects->isEmpty() || $teachers->isEmpty()) {
            $this->command->error('No subjects or teachers found. Please seed them first.');
            return;
        }

        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        foreach ($rombels as $rombel) {
            foreach ($days as $day) {
                // Friday has fewer schedules
                $count = ($day === 'jumat') ? 2 : 4;

                $startTime = Carbon::createFromTimeString('07:30:00');

                for ($i = 0; $i < $count; $i++) {
                    $endTime = $startTime->copy()->addMinutes(90);

                    Schedule::create([
                        'rombongan_belajar_id' => $rombel->id,
                        'subject_id' => $subjects->random()->id,
                        'teacher_id' => $teachers->random()->id,
                        'hari' => $day,
                        'jam_mulai' => $startTime->toTimeString(),
                        'jam_selesai' => $endTime->toTimeString(),
                    ]);

                    $startTime = $endTime->copy()->addMinutes(15); // 15 mins break
                }
            }
        }

        $this->command->info('Dummy schedules created successfully!');
    }
}
