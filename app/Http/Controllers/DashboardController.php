<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\PesertaDidik;
use App\Models\AnggotaRombel;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $schedules = [];

        if ($user->role === 'siswa') {
            $peserta = PesertaDidik::where('user_id', $user->id)->first();

            if ($peserta) {
                $anggotaRombel = AnggotaRombel::where('peserta_didik_id', $peserta->id)
                    ->latest('id')
                    ->first();

                if ($anggotaRombel) {
                    $hariIndo = $this->translateHari(strtolower(Carbon::now()->englishDayOfWeek));

                    $schedules = Schedule::with(['subject', 'teacher.user'])
                        ->where('rombongan_belajar_id', $anggotaRombel->rombongan_belajar_id)
                        ->where('hari', $hariIndo)
                        ->orderBy('jam_mulai', 'asc')
                        ->get();
                }
            }
        }

        return view('dashboard.index', compact('schedules'));
    }

    private function translateHari($day)
    {
        $map = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu'
        ];
        return $map[$day] ?? $day;
    }
}
