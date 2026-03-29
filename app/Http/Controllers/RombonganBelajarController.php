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

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->filled('angkatan')) {
            $romanMap = ['10' => 'X', '11' => 'XI', '12' => 'XII'];
            $roman = $romanMap[$request->angkatan] ?? $request->angkatan;

            $query->where(function ($q) use ($roman) {
                $q->where('nama_rombel', 'like', $roman . ' %')
                    ->orWhere('nama_rombel', 'like', $roman . '-%')
                    ->orWhere('nama_rombel', $roman);
            });
        }

        $sort = $request->get('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $rombels = $query->paginate(15)->withQueryString();

        $tahunAjars = TahunAjar::orderBy('created_at', 'desc')->get();
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $tahunAjarAktif = $this->tahunAjarAktif();

        return view('rombongan_belajar.index', compact(
            'rombels',
            'tahunAjars',
            'jurusans',
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

        $schedules = $rombonganBelajar->schedules()
            ->with(['subject', 'teacher.user'])
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');

        // Detailed presence lists for Today
        $todayDate = now()->toDateString();
        $todayAttendance = \App\Models\Attendance::whereIn('anggota_rombel_id', $anggotaIds)
            ->where('tanggal', $todayDate)
            ->where('jenis_absensi', 'masuk')
            ->get()
            ->keyBy('anggota_rombel_id');

        $presenceLists = [
            'hadir' => [],
            'terlambat' => [],
            'izin_sakit' => [],
            'alpha' => [],
            'belum' => [],
        ];

        foreach ($anggota as $item) {
            $att = $todayAttendance->get($item->id);
            if ($att) {
                if ($att->status === 'hadir') {
                    $presenceLists['hadir'][] = $item;
                } elseif ($att->status === 'terlambat') {
                    $presenceLists['terlambat'][] = $item;
                } elseif (in_array($att->status, ['izin', 'sakit'])) {
                    $presenceLists['izin_sakit'][] = $item;
                } elseif ($att->status === 'alpha') {
                    $presenceLists['alpha'][] = $item;
                }
            } else {
                $presenceLists['belum'][] = $item;
            }
        }

        // Rata-rata Penilaian untuk Admin & Walas/Anggota
        $studentUserIds = $anggota->pluck('pesertaDidik.user_id');
        $detailsAssessed = \App\Models\AssessmentDetail::whereHas('assessment', function($q) use ($studentUserIds) {
                $q->whereIn('evaluatee_id', $studentUserIds);
            })
            ->get()
            ->groupBy('category_id');

        // Hanya ambil kategori yang ditujukan untuk 'siswa' dan yang saat ini aktif.
        $studentCategories = \App\Models\AssessmentCategory::where('type', 'siswa')
                                ->where('is_active', true)
                                ->get();

        $averageAssessment = $studentCategories->map(function($category) use ($detailsAssessed) {
            $details = $detailsAssessed->get($category->id);
            return [
                'name' => $category->name,
                'score' => $details ? round($details->avg('score'), 1) : 0
            ];
        });

        return view('rombongan_belajar.show', [
            'rombel' => $rombonganBelajar,
            'anggota' => $anggota,
            'siswaAvailable' => $siswaAvailable,
            'stats' => $stats,
            'percentage' => $percentage,
            'period' => $period,
            'isWalas' => $isWalas,
            'schedules' => $schedules,
            'presenceLists' => $presenceLists,
            'averageAssessment' => $averageAssessment
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
