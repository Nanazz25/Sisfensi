<?php

namespace App\Http\Controllers;

use App\Models\RombonganBelajar;
use App\Models\TahunAjar;
use App\Models\Teacher;
use App\Models\PesertaDidik;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RombonganBelajarController extends Controller
{
    private function tahunAjarAktif()
    {
        return TahunAjar::where('is_active', true)->first();
    }

    public function index(Request $request)
    {
        $query = RombonganBelajar::with(['tahunAjar', 'waliKelas']);

        if ($request->filled('q')) {
            $query->where('nama_rombel', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('tahun_ajar_id')) {
            $query->where('tahun_ajar_id', $request->tahun_ajar_id);
        } elseif ($aktif = $this->tahunAjarAktif()) {
            $query->where('tahun_ajar_id', $aktif->id);
        }

        $sort = $request->get('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $rombels = $query->paginate(10)->withQueryString();

        $tahunAjars = TahunAjar::orderBy('created_at', 'desc')->get();
        $tahunAjarAktif = $this->tahunAjarAktif();

        return view('rombongan_belajar.index', compact(
            'rombels',
            'tahunAjars',
            'tahunAjarAktif'
        ));
    }

    public function show(Request $request, RombonganBelajar $rombonganBelajar)
    {
        $user = auth()->user();

        // Security Check for Students
        if ($user->role === 'siswa') {
            $siswa = $user->pesertaDidik;
            $isMember = $rombonganBelajar->anggotaRombel()->where('peserta_didik_id', $siswa->id)->exists();
            if (!$isMember) {
                abort(403, 'Anda bukan anggota kelas ini.');
            }
        }

        // Security Check for Guru (Bisa lihat semua rombel, tapi nanti list murid difilter di view)
        $isWalas = true;
        if ($user->role === 'guru') {
            $isWalas = $rombonganBelajar->wali_kelas_id === ($user->teacher->id ?? 0);
        }

        $period = $request->get('period', 'today');
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };
        $endDate = now()->endOfDay();

        $anggota = $rombonganBelajar->anggotaRombel()
            ->with('pesertaDidik.user')
            ->get();

        $anggotaIds = $anggota->pluck('id');

        // Stats Presensi
        $attendance = \App\Models\Attendance::whereIn('anggota_rombel_id', $anggotaIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $stats = [
            'hadir' => $attendance->where('status', 'hadir')->count(),
            'terlambat' => $attendance->where('status', 'terlambat')->count(),
            'izin_sakit' => $attendance->whereIn('status', ['izin', 'sakit'])->count(),
            'alpha' => $attendance->where('status', 'alpha')->count(),
        ];

        // Hitung persentase kehadiran (Sederhana: Hadir / Total Anggota)
        $totalAnggota = $anggota->count();
        $presentCount = $stats['hadir'] + $stats['terlambat'];
        $percentage = $totalAnggota > 0 ? round(($presentCount / ($totalAnggota * ($period === 'today' ? 1 : $startDate->diffInDays($endDate) + 1))) * 100, 1) : 0;
        // Sebenarnya persentase bisa lebih kompleks, tapi kita ikuti permintaan user "berapa % siswa hadir"

        $tahunAjarId = $rombonganBelajar->tahun_ajar_id;

        $siswaAvailable = PesertaDidik::whereDoesntHave(
            'anggotaRombel.rombonganBelajar',
            function ($q) use ($tahunAjarId) {
                $q->where('tahun_ajar_id', $tahunAjarId);
            }
        )->with('user:id,name')->get();

        return view('rombongan_belajar.show', [
            'rombel' => $rombonganBelajar,
            'anggota' => $anggota,
            'siswaAvailable' => $siswaAvailable,
            'stats' => $stats,
            'percentage' => $percentage,
            'period' => $period,
            'isWalas' => $isWalas
        ]);
    }

    public function create()
    {
        return view('rombongan_belajar.form', [
            'rombel' => null,
            'tahunAjars' => TahunAjar::orderBy('created_at', 'desc')->get(),
            'teachers' => Teacher::with('user')->get(),
            'jurusans' => Jurusan::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_rombel' => [
                'required',
                Rule::unique('rombongan_belajar')
                    ->where('tahun_ajar_id', $request->tahun_ajar_id)
            ],
            'tahun_ajar_id' => 'required|exists:tahun_ajar,id',
            'jurusan_id' => 'required|exists:jurusan,id',
            'wali_kelas_id' => 'nullable|exists:teachers,id',
        ]);

        RombonganBelajar::create($request->only(
            'nama_rombel',
            'tahun_ajar_id',
            'jurusan_id',
            'wali_kelas_id'
        ));

        return redirect()->route('rombongan-belajar.index')
            ->with('success', 'Rombongan belajar berhasil ditambahkan');
    }

    public function edit(RombonganBelajar $rombonganBelajar)
    {
        return view('rombongan_belajar.form', [
            'rombel' => $rombonganBelajar,
            'tahunAjars' => TahunAjar::orderBy('created_at', 'desc')->get(),
            'teachers' => Teacher::with('user')->get(),
            'jurusans' => Jurusan::all(),
        ]);
    }

    public function update(Request $request, RombonganBelajar $rombonganBelajar)
    {
        $request->validate([
            'nama_rombel' => [
                'required',
                Rule::unique('rombongan_belajar')
                    ->where('tahun_ajar_id', $request->tahun_ajar_id)
                    ->ignore($rombonganBelajar->id),
            ],
            'tahun_ajar_id' => 'required|exists:tahun_ajar,id',
            'jurusan_id' => 'required|exists:jurusan,id',
            'wali_kelas_id' => 'nullable|exists:teachers,id',
        ]);

        $rombonganBelajar->update($request->only(
            'nama_rombel',
            'tahun_ajar_id',
            'jurusan_id',
            'wali_kelas_id'
        ));

        return redirect()->route('rombongan-belajar.index')
            ->with('success', 'Rombongan belajar berhasil diperbarui');
    }

    public function destroy(RombonganBelajar $rombonganBelajar)
    {
        $rombonganBelajar->delete();
        return back()->with('success', 'Rombongan belajar berhasil dihapus');
    }
}
