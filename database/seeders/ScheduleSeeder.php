<?php

namespace Database\Seeders;

use App\Models\RombonganBelajar;
use App\Models\PesertaDidik;
use App\Models\AnggotaRombel;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rombels = RombonganBelajar::all();
        $students = PesertaDidik::all();
        $teachers = Teacher::all();
        $subjects = Subject::all();

        // 1. Menugaskan Siswa ke Kelas (Anggota Rombel)
        // Bagikan 30 siswa ke dalam rombel yang tersedia (misalnya, 3 rombel -> 10 siswa per rombel)
        $studentIndex = 0;
        foreach ($rombels as $rombel) {
            for ($i = 0; $i < 10; $i++) {
                if ($studentIndex < $students->count()) {
                    AnggotaRombel::create([
                        'rombongan_belajar_id' => $rombel->id,
                        'peserta_didik_id' => $students[$studentIndex]->id,
                        'created_at' => now(),
                    ]);
                    $studentIndex++;
                }
            }

            // 2. Membuat Jadwal untuk Rombel Ini
            // Buat jadwal untuk setiap mata pelajaran pada hari yang berbeda
            $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
            foreach ($subjects as $index => $subject) {
                Schedule::create([
                    'rombongan_belajar_id' => $rombel->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teachers->random()->id,
                    'hari' => $days[$index % count($days)], // Sebarkan selama beberapa hari
                    'jam_mulai' => '07:00:00',
                    'jam_selesai' => '08:30:00',
                ]);
            }
        }
    }
}
