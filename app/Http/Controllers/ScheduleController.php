<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\RombonganBelajar;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = RombonganBelajar::with(['tahunAjar', 'waliKelas.user', 'jurusan']);

        // Filter Search
        if ($request->filled('search')) {
            $query->where('nama_rombel', 'like', '%' . $request->search . '%');
        }

        // Filter Jurusan
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        // Filter Tahun Ajar
        if ($request->filled('tahun_ajar_id')) {
            $query->where('tahun_ajar_id', $request->tahun_ajar_id);
        }

        $rombels = $query->get()
            ->groupBy(function ($item) {
                return $item->tahunAjar->nama ?? 'Lainnya';
            });

        $jurusans = \App\Models\Jurusan::all();
        $tahunAjars = \App\Models\TahunAjar::all();

        return view('schedules.index', compact('rombels', 'jurusans', 'tahunAjars'));
    }

    public function show($id)
    {
        $rombel = RombonganBelajar::with(['tahunAjar', 'waliKelas.user'])->findOrFail($id);

        // Get active school days from settings
        $schoolDaysStr = \App\Models\SchoolSetting::where('key', 'hari_sekolah')->first()->value ?? 'senin,selasa,rabu,kamis,jumat';
        $schoolDays = explode(',', $schoolDaysStr);

        $schedules = Schedule::with(['subject', 'teacher.user'])
            ->where('rombongan_belajar_id', $id)
            ->whereIn('hari', $schoolDays)
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');

        return view('schedules.show', compact('rombel', 'schedules', 'schoolDays'));
    }

    public function mySchedules()
    {
        $user = auth()->user();
        if ($user->role !== 'guru' || !$user->teacher) {
            abort(403, 'Akses khusus Guru');
        }

        $activeTahunAjarId = \App\Models\TahunAjar::where('is_active', true)->value('id');

        // Get active school days from settings
        $schoolDaysStr = \App\Models\SchoolSetting::where('key', 'hari_sekolah')->first()->value ?? 'senin,selasa,rabu,kamis,jumat';
        $schoolDays = explode(',', $schoolDaysStr);

        $schedules = Schedule::with(['subject', 'rombonganBelajar.jurusan'])
            ->whereHas('rombonganBelajar', function ($query) use ($activeTahunAjarId) {
                $query->where('tahun_ajar_id', $activeTahunAjarId);
            })
            ->where('teacher_id', $user->teacher->id)
            ->whereIn('hari', $schoolDays)
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');

        return view('schedules.my_schedules', compact('schedules', 'schoolDays'));
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
