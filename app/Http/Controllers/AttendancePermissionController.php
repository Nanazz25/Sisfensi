<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendancePermission;
use App\Models\Attendance;
use App\Models\AnggotaRombel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AttendancePermissionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AttendancePermission::with(['anggotaRombel.pesertaDidik.user', 'anggotaRombel.rombonganBelajar', 'approver']);

        // Siswa melihat pengajuan izin/sakitnya sendiri
        if ($user->role === 'siswa') {
            $query->whereHas('anggotaRombel', function ($q) use ($user) {
                $q->where('peserta_didik_id', $user->pesertaDidik->id);
            });
        } elseif ($user->role === 'guru') {
            // Menampilkan siswa yang berada di kelas binaannya
            $query->whereHas('anggotaRombel.rombonganBelajar', function ($rq) use ($user) {
                $rq->where('wali_kelas_id', $user->teacher->id ?? 0);
            });
        }
        // Admin melihat semua
        if ($user->role === 'admin') {
            if ($request->filled('class_id')) {
                $query->whereHas('anggotaRombel', function($q) use ($request) {
                    $q->where('rombongan_belajar_id', $request->class_id);
                });
            }

            if ($request->filled('level')) {
                $query->whereHas('anggotaRombel.rombonganBelajar', function($q) use ($request) {
                    $q->where('nama_rombel', 'like', $request->level . ' %');
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('anggotaRombel.pesertaDidik.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $permissions = $query->latest()->paginate(10);
        $permissions->appends($request->all());

        // Ambil data rombel untuk filter admin
        $classes = ($user->role === 'admin') ? \App\Models\RombonganBelajar::orderBy('nama_rombel')->get() : collect();

        // Menghitung frekuensi untuk peringatan (Tampilan Guru/Admin)
        if (auth()->user()->role !== 'siswa') {
            foreach ($permissions as $permit) {
                $monthStart = Carbon::now()->startOfMonth();
                $monthEnd = Carbon::now()->endOfMonth();
                $permit->monthly_count = AttendancePermission::where('anggota_rombel_id', $permit->anggota_rombel_id)
                    ->whereIn('status', ['approved', 'pending'])
                    ->whereIn('jenis', ['izin', 'sakit'])
                    ->whereBetween('tanggal_mulai', [$monthStart, $monthEnd])
                    ->count();
            }
        }

        return view('attendance_permissions.index', compact('permissions', 'classes'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $monthlyCount = 0;

        if ($user->role === 'siswa') {
            $anggotaRombel = $user->pesertaDidik->anggotaRombel()->latest()->first();

            if ($anggotaRombel) {
                $today = Carbon::today()->toDateString();

                // Cek apakah sudah ada absen "masuk" hari ini
                $alreadyAttended = Attendance::where('anggota_rombel_id', $anggotaRombel->id)
                    ->where('tanggal', $today)
                    ->where('jenis_absensi', 'masuk')
                    ->exists();

                if ($alreadyAttended) {
                    return redirect()->route('attendance-permissions.index')
                        ->with('error', 'Anda sudah melakukan absensi hari ini.');
                }

                // Cek pengajuan pending hari ini
                $pendingPermission = AttendancePermission::where('anggota_rombel_id', $anggotaRombel->id)
                    ->where('status', 'pending')
                    ->whereDate('tanggal_mulai', $today)
                    ->exists();

                if ($pendingPermission) {
                    return redirect()->route('attendance-permissions.index')
                        ->with('error', 'Anda memiliki pengajuan yang masih dalam proses untuk hari ini.');
                }

                // Hitung frekuensi izin/sakit bulan ini
                $monthStart = Carbon::now()->startOfMonth();
                $monthEnd = Carbon::now()->endOfMonth();
                $monthlyCount = AttendancePermission::where('anggota_rombel_id', $anggotaRombel->id)
                    ->whereIn('status', ['approved', 'pending'])
                    ->whereIn('jenis', ['izin', 'sakit'])
                    ->whereBetween('tanggal_mulai', [$monthStart, $monthEnd])
                    ->count();
            }
        }

        $jamPulang = \App\Models\SchoolSetting::where('key', 'jam_pulang')->value('value') ?? '15:00';

        return view('attendance_permissions.create', compact('request', 'monthlyCount', 'jamPulang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|same:tanggal_mulai',
            'jenis' => 'required|in:izin,sakit,manual',
            'keterangan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'jenis_absensi_manual' => 'nullable|in:masuk,pulang,mapel',
        ]);

        $user = auth()->user();
        $anggotaRombel = $user->pesertaDidik->anggotaRombel()->latest()->first();

        if (!$anggotaRombel) {
            return back()->with('error', 'Anda tidak terdaftar di kelas manapun.');
        }

        // Batasi absen manual: Tidak bisa dilakukan jika sudah lewat jam pulang
        if ($request->jenis === 'manual') {
            $jamPulang = \App\Models\SchoolSetting::where('key', 'jam_pulang')->value('value') ?? '15:00';
            if (now()->toTimeString() >= $jamPulang) {
                return back()->with('error', 'Pengajuan absen manual tidak dapat dilakukan setelah jam pulang (' . substr($jamPulang, 0, 5) . ').');
            }
        }

        $start = Carbon::parse($request->tanggal_mulai);
        $end = Carbon::parse($request->tanggal_selesai);

        $existingAttendance = Attendance::where('anggota_rombel_id', $anggotaRombel->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('jenis_absensi', 'masuk')
            ->exists();

        if ($existingAttendance) {
            return back()->with('error', 'Tidak dapat mengajukan izin/sakit karena Anda sudah melakukan absensi pada rentang tanggal tersebut.');
        }

        // Cek pengajuan pending yang tumpang tindih
        $overlappingPermission = AttendancePermission::where('anggota_rombel_id', $anggotaRombel->id)
            ->where('status', 'pending')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('tanggal_mulai', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('tanggal_selesai', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('tanggal_mulai', '<=', $start->toDateString())
                            ->where('tanggal_selesai', '>=', $end->toDateString());
                    });
            })
            ->exists();

        if ($overlappingPermission) {
            return back()->with('error', 'Anda sudah memiliki pengajuan yang masih dalam proses (pending) pada rentang tanggal tersebut.');
        }

        $data = $request->all();
        $data['anggota_rombel_id'] = $anggotaRombel->id;
        $data['status'] = 'pending';

        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('permissions', 'public');
            $data['lampiran'] = $path;
        }

        AttendancePermission::create($data);

        return redirect()->route('attendance-permissions.index')->with('success', 'Permohonan izin berhasil diajukan.');
    }

    public function updateStatus(AttendancePermission $permission, $status)
    {
        if (!in_array($status, ['approved', 'rejected'])) {
            return back()->with('error', 'Status tidak valid.');
        }

        $permission->update([
            'status' => $status,
            'approved_by' => auth()->id(),
        ]);

        if ($status === 'approved') {
            // Sinkronkan ke tabel Attendance
            $start = Carbon::parse($permission->tanggal_mulai);
            $end = Carbon::parse($permission->tanggal_selesai);

            while ($start <= $end) {
                $statusAttendance = $permission->jenis; // 'izin' or 'sakit'
                if ($permission->jenis === 'manual') {
                    $statusAttendance = 'hadir';
                }

                Attendance::updateOrCreate(
                    [
                        'anggota_rombel_id' => $permission->anggota_rombel_id,
                        'tanggal' => $start->toDateString()
                    ],
                    [
                        'waktu_absen' => $permission->jenis === 'manual' ? $permission->created_at : now(),
                        'jenis_absensi' => $permission->jenis === 'manual' ? ($permission->jenis_absensi_manual ?? 'masuk') : 'masuk',
                        'status' => $statusAttendance,
                        'metode' => 'manual',
                        'remarks' => $permission->keterangan
                    ]
                );
                $start->addDay();
            }
        } else {
            // Jika ditolak, hapus record attendance yang terkait (jika ada)
            Attendance::where('anggota_rombel_id', $permission->anggota_rombel_id)
                ->whereBetween('tanggal', [$permission->tanggal_mulai, $permission->tanggal_selesai])
                ->whereIn('status', ['izin', 'sakit', 'hadir'])
                ->where('metode', 'manual')
                ->delete();
        }

        return back()->with('success', 'Status permohonan berhasil diperbarui.');
    }
}
