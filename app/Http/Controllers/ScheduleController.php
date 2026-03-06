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

        $conflict = Schedule::where('rombongan_belajar_id', $request->rombongan_belajar_id)
            ->where('hari', $request->hari)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('jam_mulai', '<=', $request->jam_mulai)
                            ->where('jam_selesai', '>=', $request->jam_selesai);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors(['jam_mulai' => 'Jadwal bentrok dengan jam pelajaran lain di kelas yang sama!']);
        }

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

        $conflict = Schedule::where('rombongan_belajar_id', $request->rombongan_belajar_id)
            ->where('hari', $request->hari)
            ->where('id', '!=', $schedule->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('jam_mulai', '<=', $request->jam_mulai)
                            ->where('jam_selesai', '>=', $request->jam_selesai);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors(['jam_mulai' => 'Jadwal bentrok dengan jam pelajaran lain di kelas yang sama!']);
        }

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
