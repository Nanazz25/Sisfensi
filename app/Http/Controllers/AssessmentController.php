<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentDetail;
use App\Models\User;
use App\Models\Teacher;
use App\Models\PesertaDidik;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $q = $request->input('q');
        $sort = $request->input('sort', 'desc');
        $periodFilter = $request->input('period');

        $query = User::query();

        if ($user->role === 'admin') {
            // Admin menilai guru
            $query->where('role', 'guru')->with(['teacher']);
        } elseif ($user->role === 'guru') {
            // Guru menilai siswa yang ada di kelasnya
            $teacher = $user->teacher;
            if ($teacher) {
                $studentIds = $teacher->rombonganBelajar->flatMap(function($rombel) {
                    return $rombel->anggotaRombel->pluck('peserta_didik_id');
                });
                
                $query->whereHas('pesertaDidik', function($q_sub) use ($studentIds) {
                    $q_sub->whereIn('id', $studentIds);
                })->with('pesertaDidik');
            } else {
                $query->where('id', 0);
            }
        } elseif ($user->role === 'siswa') {
            // Siswa hanya bisa melihat nilai sendiri
            return redirect()->route('assessment.show', $user->id);
        }

        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }

        // Ambil daftar periode yang tersedia untuk filter
        $availablePeriods = Assessment::distinct()->pluck('period');

        // Kalkulasi Progres
        $totalTargetsQuery = clone $query;
        $totalTargets = $totalTargetsQuery->count();
        
        $monthStart = now()->startOfMonth();
        $assessedQuery = (clone $query)->whereHas('receivedAssessments', function($a) use ($monthStart, $periodFilter) {
            if ($periodFilter) {
                $a->where('period', $periodFilter);
            } else {
                $a->where('assessment_date', '>=', $monthStart);
            }
        });

        $assessedCount = $assessedQuery->count();
        $progressPercent = $totalTargets > 0 ? round(($assessedCount / $totalTargets) * 100) : 0;

        // Pengurutan: Yang sudah dinilai pindah ke atas, lalu berdasarkan tanggal buat
        $query->withExists(['receivedAssessments as is_assessed_in_period' => function($a) use ($monthStart, $periodFilter) {
            if ($periodFilter) {
                $a->where('period', $periodFilter);
            } else {
                $a->where('assessment_date', '>=', $monthStart);
            }
        }])
        ->orderBy('is_assessed_in_period', 'desc') // Sudah dinilai (1) sebelum Belum dinilai (0)
        ->orderBy('created_at', $sort);

        $evaluatees = $query->paginate(10)->withQueryString();

        // Ambil info penilaian terakhir dan Hitung Rata-rata untuk Statistik
        $evaluateeIds = $query->pluck('id');
        
        foreach ($evaluatees as $evaluatee) {
            $evaluatee->last_assessment = Assessment::where('evaluatee_id', $evaluatee->id)
                ->where('evaluator_id', $user->id)
                ->latest()
                ->first();
        }

        // Data Statistik Grafik Rata-rata Keseluruhan (Mengikuti Filter Periode atau Bulan Ini)
        $avgScoresQuery = AssessmentDetail::whereHas('assessment', function($a) use ($evaluateeIds, $periodFilter) {
            $a->whereIn('evaluatee_id', $evaluateeIds);
            if ($periodFilter) {
                $a->where('period', $periodFilter);
            } else {
                $a->where('assessment_date', '>=', now()->startOfMonth());
            }
        })->whereHas('category', function($cat) use ($user) {
            $type = ($user->role === 'admin') ? 'guru' : 'siswa';
            $cat->where('type', $type);
        });

        $avgScores = $avgScoresQuery->select('category_id', DB::raw('AVG(score) as avg_score'))
            ->groupBy('category_id')
            ->with('category')
            ->get();

        $averageChartData = $avgScores->map(function($item) {
            return [
                'name' => $item->category->name ?? 'Kategori Dihapus',
                'score' => round($item->avg_score, 1)
            ];
        });

        return view('assessment.index', compact(
            'evaluatees', 
            'totalTargets', 
            'assessedCount', 
            'progressPercent', 
            'availablePeriods', 
            'periodFilter',
            'averageChartData'
        ));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Admin menilai guru, Guru menilai siswa
        $type = ($user->role === 'admin') ? 'guru' : 'siswa';

        $categories = AssessmentCategory::where('is_active', true)
            ->where('type', $type)
            ->orderBy('name')
            ->get();

        // Ambil pengaturan sekolah untuk menentukan hari kerja
        $schoolDays = explode(',', SchoolSetting::getValue('hari_sekolah', 'senin,selasa,rabu,kamis,jumat'));
        $dayMap = [
            'senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6, 'minggu' => 0
        ];
        
        $opDays = array_map(fn($d) => $dayMap[trim($d)] ?? 0, $schoolDays);
        sort($opDays);

        // Siapkan pilihan Periode
        $periods = [];
        $now = now();
        
        // Periode Harian
        $periods[] = "Harian - " . $now->translatedFormat('d F Y');
        
        // Periode Mingguan (Berdasarkan hari sekolah)
        $startOfWeekNum = min($opDays);
        $endOfWeekNum = max($opDays);
        
        // Carbon startOfWeek is Monday (1). We adjust based on school settings.
        // If Senin (1) is min, we add 0 days to Monday.
        // Handle edge case where end is less than start (e.g. Sunday=0 as max, Mon=1 as min) - though unlikely with school days
        $sw = $now->copy()->startOfWeek($startOfWeekNum);
        $ew = $now->copy()->startOfWeek($startOfWeekNum)->addDays($endOfWeekNum - $startOfWeekNum);
        
        if ($endOfWeekNum < $startOfWeekNum) {
            $ew = $now->copy()->endOfWeek(0);
        }
        
        $periods[] = "Mingguan - (" . $sw->translatedFormat('d M') . " s/d " . $ew->translatedFormat('d M Y') . ")";
        
        // Periode Bulanan
        $periods[] = "Bulanan - " . $now->translatedFormat('F Y');
        
        // Periode Semester dari TahunAjar yang aktif
        $activeTA = \App\Models\TahunAjar::where('is_active', true)->latest()->first();
        if ($activeTA) {
            $periods[] = "Semester " . $activeTA->semester . " (" . $activeTA->nama . ")";
        }

        // Daftar subjek yang bisa dinilai
        $evaluatees = collect();
        if ($user->role === 'admin') {
            $evaluatees = User::where('role', 'guru')->with('teacher')->get();
        } elseif ($user->role === 'guru') {
            $teacher = $user->teacher;
            if ($teacher) {
                $studentIds = $teacher->rombonganBelajar->flatMap(function($rombel) {
                    return $rombel->anggotaRombel->pluck('peserta_didik_id');
                });
                $evaluatees = User::whereHas('pesertaDidik', function($q_sub) use ($studentIds) {
                    $q_sub->whereIn('id', $studentIds);
                })->with('pesertaDidik')->get();
            }
        }

        return view('assessment.create', compact('evaluatees', 'categories', 'periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'evaluatee_ids' => 'required|array',
            'evaluatee_ids.*' => 'exists:users,id',
            'scores' => 'required|array',
            'scores.*' => 'integer|min:0|max:10',
            'assessment_date' => 'required|date',
            'period' => 'required|string',
            'general_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->evaluatee_ids as $evaluateeId) {
                // Buat satu record parent Assessment untuk tiap siswa/guru yang dinilai
                $assessment = Assessment::create([
                    'evaluator_id' => Auth::id(),
                    'evaluatee_id' => $evaluateeId,
                    'assessment_date' => $request->assessment_date,
                    'period' => $request->period,
                    'general_notes' => $request->general_notes,
                ]);

                // Simpan rincian skor ke tabel assessment_details
                foreach ($request->scores as $categoryId => $score) {
                    if ($score > 0) {
                        AssessmentDetail::create([
                            'assessment_id' => $assessment->id,
                            'category_id' => $categoryId,
                            'score' => $score,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('assessment.index')
                ->with('success', 'Penilaian massal berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $q = $request->input('q');
        $sort = $request->input('sort', 'desc');
        $periodFilter = $request->input('period');
        
        if ($user->role === 'siswa' && $user->id != $id) {
            abort(403, 'Anda hanya dapat melihat nilai Anda sendiri.');
        }

        $evaluatee = User::with(['pesertaDidik', 'teacher'])->findOrFail($id);

        // Ambil semua periode yang tersedia untuk dropdown filter
        $availablePeriods = Assessment::where('evaluatee_id', $id)
            ->whereNotNull('period')
            ->distinct()
            ->pluck('period');

        $query = Assessment::where('evaluatee_id', $id)
            ->with(['evaluator', 'details.category']);

        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('period', 'like', "%{$q}%")
                    ->orWhere('general_notes', 'like', "%{$q}%")
                    ->orWhereHas('details.category', function($cat) use ($q) {
                        $cat->where('name', 'like', "%{$q}%");
                    });
            });
        }

        if ($periodFilter) {
            $query->where('period', $periodFilter);
        }

        $query->orderBy('assessment_date', $sort);
        $assessments = $query->paginate(10)->withQueryString();

        // Data Radar (Skor terbaru per kategori)
        $type = ($evaluatee->role === 'guru') ? 'guru' : 'siswa';
        
        // Ambil kategori yang aktif ATAU yang memang pernah dinilai untuk user ini (History)
        $assessedCategoryIds = AssessmentDetail::whereHas('assessment', function($a) use ($id) {
                $a->where('evaluatee_id', $id);
            })->distinct()->pluck('category_id');
        
        $allCategories = AssessmentCategory::where('type', $type)
            ->where(function($q) use ($assessedCategoryIds) {
                $q->where('is_active', true)
                  ->orWhereIn('id', $assessedCategoryIds);
            })
            ->orderBy('name')
            ->get();

        $latestScoresMap = AssessmentDetail::whereHas('assessment', function($a) use ($id, $periodFilter) {
                $a->where('evaluatee_id', $id);
                if ($periodFilter) {
                    $a->where('period', $periodFilter);
                }
            })
            ->whereIn('id', function($q_sub) use ($id, $periodFilter) {
                $q_sub->selectRaw('MAX(ad.id)')
                    ->from('assessment_details as ad')
                    ->join('assessments as a', 'ad.assessment_id', '=', 'a.id')
                    ->where('a.evaluatee_id', $id);
                if ($periodFilter) {
                    $q_sub->where('a.period', $periodFilter);
                }
                $q_sub->groupBy('ad.category_id');
            })
            ->pluck('score', 'category_id');

        $radarData = $allCategories->map(function($cat) use ($latestScoresMap) {
            return [
                'name' => $cat->name,
                'score' => $latestScoresMap[$cat->id] ?? 0,
            ];
        });

        return view('assessment.show', compact('evaluatee', 'assessments', 'radarData', 'availablePeriods', 'periodFilter'));
    }
}
