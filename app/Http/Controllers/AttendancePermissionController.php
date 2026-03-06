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

        if ($user->role === 'siswa') {
            $query->whereHas('anggotaRombel', function ($q) use ($user) {
                $q->where('peserta_didik_id', $user->pesertaDidik->id);
            });
        } elseif ($user->role === 'guru') {
            $query->whereHas('anggotaRombel', function ($q) use ($user) {
                $q->where('rombongan_belajar_id', $user->teacher->id ?? 0); // Walas logic can be refined
                // Or check wali_kelas_id in rombongan_belajar
                $q->whereHas('rombonganBelajar', function ($rq) use ($user) {
                    $rq->where('wali_kelas_id', $user->teacher->id ?? 0);
                });
            });
        }
        // Admin sees all
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
        return view('attendance_permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('attendance_permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis' => 'required|in:izin,sakit',
            'keterangan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = auth()->user();
        $anggotaRombel = $user->pesertaDidik->anggotaRombel()->latest()->first();

        if (!$anggotaRombel) {
            return back()->with('error', 'Anda tidak terdaftar di kelas manapun.');
        }

        $start = Carbon::parse($request->tanggal_mulai);
        $end = Carbon::parse($request->tanggal_selesai);

        $existingAttendance = Attendance::where('anggota_rombel_id', $anggotaRombel->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('jenis_absensi', 'masuk')
            ->exists();

        if ($existingAttendance) {
            return back()->with('error', 'Tidak dapat mengajukan izin/sakit karena Anda sudah melakukan absensi pada tanggal tersebut.');
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
                Attendance::updateOrCreate(
                    [
                        'anggota_rombel_id' => $permission->anggota_rombel_id,
                        'tanggal' => $start->toDateString()
                    ],
                    [
                        'waktu_absen' => now(),
                        'jenis_absensi' => 'masuk',
                        'status' => $permission->jenis, // 'izin' or 'sakit'
                        'metode' => 'manual'
                    ]
                );
                $start->addDay();
            }
        } else {
            // Jika ditolak, hapus record attendance yang terkait (jika ada)
            Attendance::where('anggota_rombel_id', $permission->anggota_rombel_id)
                ->whereBetween('tanggal', [$permission->tanggal_mulai, $permission->tanggal_selesai])
                ->whereIn('status', ['izin', 'sakit'])
                ->delete();
        }

        return back()->with('success', 'Status permohonan berhasil diperbarui.');
    }
}
