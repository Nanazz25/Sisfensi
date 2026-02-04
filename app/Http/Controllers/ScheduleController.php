<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\RombonganBelajar;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with([
            'rombonganBelajar',
            'subject',
            'teacher.user'
        ])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('schedules.index', compact('schedules'));
    }

    public function create()
    {
        return view('schedules.form', [
            'rombels' => RombonganBelajar::all(),
            'subjects' => Subject::all(),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rombongan_belajar_id' => 'required|exists:rombongan_belajar,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
        ]);

        Schedule::create($request->all());

        return redirect()
            ->route('schedules.index')
            ->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function edit(Schedule $schedule)
    {
        return view('schedules.form', [
            'schedule' => $schedule,
            'rombels' => RombonganBelajar::all(),
            'subjects' => Subject::all(),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'rombongan_belajar_id' => 'required|exists:rombongan_belajar,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
        ]);

        $schedule->update($request->all());

        return redirect()
            ->route('schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return back()
            ->with('success', 'Jadwal berhasil dihapus');
    }
}
