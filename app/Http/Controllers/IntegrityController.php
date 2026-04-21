<?php

namespace App\Http\Controllers;

use App\Models\PointRule;
use App\Models\PointLedger;
use App\Models\FlexibilityItem;
use App\Models\UserToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IntegrityController extends Controller
{
    /**
     * Dashboard Admin: Pengelola Aturan Poin & Katalog Marketplace Reward.
     * Fitur ini memungkinkan Admin untuk membuat sistem gamifikasi disiplin.
     */
    public function adminIndex(Request $request)
    {
        $ruleQuery = PointRule::query();

        // Filter Pencarian Nama
        if ($request->filled('q')) {
            $ruleQuery->where('rule_name', 'LIKE', '%' . $request->q . '%');
        }

        // Filter berdasarkan Tipe Pemicu (Otomatis/Manual/Sistem)
        if ($request->filled('trigger_type')) {
            $ruleQuery->where('trigger_type', $request->trigger_type);
        }

        // Filter berdasarkan Kategori Presensi (Masuk, Mapel, Pulang, All)
        if ($request->filled('attendance_type')) {
            $ruleQuery->where('attendance_type', $request->attendance_type);
        }

        // Filter berdasarkan Role (Siswa, Guru, etc)
        if ($request->filled('target_role')) {
            $ruleQuery->where('target_role', $request->target_role);
        }

        // Filter berdasarkan Jenis Poin (Reward/Penalti)
        if ($request->filled('point_type')) {
            if ($request->point_type === 'plus') {
                $ruleQuery->where('point_modifier', '>', 0);
            } elseif ($request->point_type === 'minus') {
                $ruleQuery->where('point_modifier', '<', 0);
            }
        }

        // Pengurutan data tabel
        $sortColumn = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $ruleQuery->orderBy($sortColumn, $sortOrder);

        // Mengambil data aturan dan item marketplace dengan sistem paginasi terpisah
        $rules = $ruleQuery->paginate(10, ['*'], 'rules_page')->withQueryString();
        $items = FlexibilityItem::paginate(10, ['*'], 'items_page');
        
        // Leaderboard: Top 10 Siswa AKTIF berdasarkan total poin tertinggi
        $activeTahunAjarId = \App\Models\TahunAjar::where('is_active', true)->value('id');
        
        $baseLeaderboard = User::where('role', 'siswa')
            ->whereHas('pesertaDidik.anggotaRombel.rombonganBelajar', function($q) use ($activeTahunAjarId) {
                $q->where('tahun_ajar_id', $activeTahunAjarId);
            });

        $topUsers = (clone $baseLeaderboard)
            ->withSum('pointLedgers as total_points', 'amount')
            ->having('total_points', '>', 0)
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        // Bottom 10: Siswa AKTIF dengan poin terendah
        $bottomUsers = (clone $baseLeaderboard)
            ->withSum('pointLedgers as total_points', 'amount')
            ->orderByRaw('IFNULL(total_points, 0) ASC')
            ->limit(10)
            ->get();

        // Mengambil Pengaturan Jam Sekolah untuk basis aturan
        $settings = \DB::table('school_settings')->whereIn('key', ['jam_masuk', 'jam_masuk_toleransi', 'jam_pulang'])->get();

        return view('integrity.admin.index', compact('rules', 'items', 'topUsers', 'bottomUsers', 'settings'));
    }

    /**
     * Menyimpan aturan poin baru (misal: Datang tepat waktu = +10 poin)
     */
    public function storeRule(Request $request)
    {
        $request->validate([
            'rule_name' => 'required',
            'target_role' => 'required',
            'trigger_type' => 'required',
            'attendance_type' => 'required|in:all,masuk,pelajaran,pulang',
            'basis_type' => 'required_if:trigger_type,attendance',
            'condition_operator' => 'required_if:basis_type,fixed,setting',
            'point_modifier' => 'required|integer',
        ]);

        PointRule::create($request->all());
        return back()->with('success', 'Aturan berhasil disimpan.');
    }

    /**
     * Mengupdate detail aturan yang sudah ada
     */
    public function updateRule(Request $request, PointRule $rule)
    {
        $request->validate([
            'rule_name' => 'required',
            'target_role' => 'required',
            'trigger_type' => 'required',
            'attendance_type' => 'required|in:all,masuk,pelajaran,pulang',
            'basis_type' => 'required_if:trigger_type,attendance',
            'condition_operator' => 'required_if:basis_type,fixed,setting',
            'point_modifier' => 'required|integer',
        ]);

        $rule->update($request->all());
        return back()->with('success', 'Aturan berhasil diperbarui.');
    }

    /**
     * Menghapus aturan integritas
     */
    public function destroyRule(PointRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Aturan berhasil dihapus.');
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'item_name' => 'required',
            'item_type' => 'required',
            'effect_value' => 'nullable|integer',
            'point_cost' => 'required|integer',
            'purchase_limit' => 'nullable|integer',
            'purchase_period' => 'required|in:none,daily,weekly,monthly',
        ]);

        FlexibilityItem::create($request->all());
        return back()->with('success', 'Item berhasil disimpan.');
    }

    /**
     * Mengupdate data item marketplace
     */
    public function updateItem(Request $request, FlexibilityItem $item)
    {
        $request->validate([
            'item_name' => 'required',
            'item_type' => 'required',
            'effect_value' => 'nullable|integer',
            'point_cost' => 'required|integer',
            'purchase_limit' => 'nullable|integer',
            'purchase_period' => 'required|in:none,daily,weekly,monthly',
        ]);

        $item->update($request->all());
        return back()->with('success', 'Item berhasil diperbarui.');
    }

    /**
     * Menghapus item dari marketplace
     */
    public function destroyItem(FlexibilityItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item berhasil dihapus.');
    }

    /**
     * Dashboard Pengguna: Dompet & Toko (untuk Siswa) atau Monitoring (untuk Guru/Admin)
     */
    public function userIndex(Request $request)
    {
        // Jika login sebagai siswa, tampilkan saldo poin, riwayat, dan daftar belanja reward
        if (Auth::user()->role === 'siswa') {
            $user = Auth::user();
            $items = FlexibilityItem::all();
            
            // Urutkan: Terakhir dipakai/dibuat muncul paling atas
            $inventory = $user->userTokens()
                ->with(['item', 'attendance'])
                ->orderByDesc('updated_at')
                ->get();
            
            // Ambil riwayat mutasi dengan filter
            $mutationQuery = PointLedger::where('user_id', $user->id);
            
            if ($request->filled('type')) {
                $mutationQuery->where('amount', $request->type === 'plus' ? '>' : '<', 0);
            }
            
            if ($request->filled('month')) {
                $mutationQuery->whereMonth('created_at', $request->month);
            }

            if ($request->filled('q')) {
                $mutationQuery->where('description', 'LIKE', '%' . $request->q . '%');
            }

            if ($request->get('sort') === 'oldest') {
                $mutationQuery->oldest();
            } else {
                $mutationQuery->latest();
            }

            $mutations = $mutationQuery->paginate(10)->withQueryString();

            // Ambil panduan aturan poin
            $pointRules = \App\Models\PointRule::where('target_role', 'siswa')
                ->orderBy('point_modifier', 'desc')
                ->get();

            return view('integrity.user.index', compact('user', 'items', 'inventory', 'mutations', 'pointRules'));
        }

        /**
         * VIEW MONITORING GURU/ADMIN:
         * Menampilkan ranking siswa dan daftar kedisiplinan seluruh siswa dengan filter canggih.
         */
        $activeTahunAjarId = \App\Models\TahunAjar::where('is_active', true)->value('id');
        $isWalas = Auth::user()->active_rombel ? true : false;
        
        // Data pendukung untuk dropdown filter
        $jurusans = \App\Models\Jurusan::all();
        $rombels = \App\Models\RombonganBelajar::where('tahun_ajar_id', $activeTahunAjarId)->get();
        $tingkats = ['10', '11', '12'];

        // Query dasar untuk mengambil data siswa pada tahun ajaran aktif
        $baseStudentQuery = User::where('role', 'siswa')
            ->whereHas('pesertaDidik.anggotaRombel.rombonganBelajar', function($q) use ($activeTahunAjarId) {
                $q->where('tahun_ajar_id', $activeTahunAjarId);
            });

        // Closure untuk menerapkan filter (Pencarian, Tingkat, Jurusan, Rombel, atau Kelas Saya)
        $applyFilters = function($query) use ($request) {
            if ($request->filled('tingkat')) {
                $romanMap = ['10' => 'X', '11' => 'XI', '12' => 'XII'];
                $roman = $romanMap[$request->tingkat] ?? $request->tingkat;
                
                $query->whereHas('pesertaDidik.anggotaRombel.rombonganBelajar', function($q) use ($roman) {
                    $q->where('nama_rombel', 'LIKE', $roman . ' %')
                      ->orWhere('nama_rombel', 'LIKE', $roman . '-%')
                      ->orWhere('nama_rombel', $roman);
                });
            }
            if ($request->filled('jurusan_id')) {
                $query->whereHas('pesertaDidik.anggotaRombel.rombonganBelajar', function($q) use ($request) {
                    $q->where('jurusan_id', $request->jurusan_id);
                });
            }
            if ($request->filled('rombel_id')) {
                $query->whereHas('pesertaDidik.anggotaRombel', function($q) use ($request) {
                    $q->where('rombongan_belajar_id', $request->rombel_id);
                });
            }
            if ($request->filled('my_class') && Auth::user()->active_rombel) {
                $query->whereHas('pesertaDidik.anggotaRombel', function($q) {
                    $q->where('rombongan_belajar_id', Auth::user()->active_rombel->id);
                });
            }
            if ($request->filled('q')) {
                $query->where('name', 'LIKE', '%' . $request->q . '%');
            }
            return $query;
        };

        // Mengambil Top Siswa (Ranking) sesuai hasil filter
        $topUsersQuery = clone $baseStudentQuery;
        $topUsers = $applyFilters($topUsersQuery)
            ->withSum('pointLedgers as total_points', 'amount')
            ->havingRaw('total_points >= 0 OR total_points IS NULL')
            ->orderByRaw('IFNULL(total_points, 0) DESC')
            ->limit(10)
            ->get();

        // Mengambil Bottom Siswa (Poin Terendah) sesuai hasil filter
        $bottomUsersQuery = clone $baseStudentQuery;
        $bottomUsers = $applyFilters($bottomUsersQuery)
            ->withSum('pointLedgers as total_points', 'amount')
            ->orderByRaw('IFNULL(total_points, 0) ASC')
            ->limit(10)
            ->get();

        // Mengambil semua daftar siswa sesuai hasil filter dengan paginasi
        $studentsQuery = clone $baseStudentQuery;
        $students = $applyFilters($studentsQuery)
            ->withSum('pointLedgers as total_points', 'amount')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $allFilteredStudentIds = $applyFilters(clone $baseStudentQuery)->pluck('users.id')->toArray();

        // Aturan manual untuk Guru saat memberikan poin di modal pop-up
        $manualRules = PointRule::where('trigger_type', 'manual')->get();

        return view('integrity.guru.monitoring', compact('topUsers', 'bottomUsers', 'students', 'isWalas', 'manualRules', 'jurusans', 'rombels', 'tingkats', 'allFilteredStudentIds'));
    }

    /**
     * Guru memberikan poin secara manual kepada siswa (misal: Siswa Sangat Sopan = +5 poin)
     * Membutuhkan alasan wajib sebagai bukti pertanggungjawaban.
     */
    public function givePointManually(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'rule_id' => 'required|exists:point_rules,id',
            'reason' => 'required|min:5',
        ]);

        $userIds = $request->user_ids;
        $rule = PointRule::findOrFail($request->rule_id);
        $author = Auth::user();

        // Jalankan transaksi database
        DB::transaction(function () use ($userIds, $rule, $request, $author) {
            foreach ($userIds as $userId) {
                $student = User::find($userId);
                if ($student && $student->role === 'siswa') {
                    $currentBalance = $student->current_points;
                    
                    PointLedger::create([
                        'user_id' => $student->id,
                        'transaction_type' => $rule->point_modifier > 0 ? 'EARN' : 'PENALTY',
                        'amount' => $rule->point_modifier,
                        'current_balance' => $currentBalance + $rule->point_modifier,
                        'description' => $rule->rule_name . ': ' . $request->reason . ' (Oleh: ' . $author->name . ')'
                    ]);
                }
            }
        });

        return back()->with('success', 'Berhasil memberikan poin kepada ' . count($userIds) . ' siswa.');
    }

    /**
     * Menukarkan Poin: Siswa membeli reward dari marketplace (mengurangi saldo poin)
     */
    public function buyItem(FlexibilityItem $item)
    {
        $user = Auth::user();
        $balance = $user->current_points;

        // 1. Cek Saldo Poin
        if ($balance < $item->point_cost) {
            return back()->with('error', 'Poin tidak mencukupi.');
        }

        // 2. Cek Batasan Pembelian (Purchase Limit)
        if ($item->purchase_limit > 0 && $item->purchase_period !== 'none') {
            $startDate = match ($item->purchase_period) {
                'daily' => now()->startOfDay(),
                'weekly' => now()->startOfWeek(),
                'monthly' => now()->startOfMonth(),
                default => null,
            };

            if ($startDate) {
                $purchaseCount = UserToken::where('user_id', $user->id)
                    ->where('item_id', $item->id)
                    ->where('created_at', '>=', $startDate)
                    ->count();

                if ($purchaseCount >= $item->purchase_limit) {
                    $periodBindo = [
                        'daily' => 'hari',
                        'weekly' => 'minggu',
                        'monthly' => 'bulan',
                    ];
                    return back()->with('error', "Limit tercapai! Item ini hanya bisa dibeli {$item->purchase_limit}x per {$periodBindo[$item->purchase_period]}.");
                }
            }
        }

        DB::transaction(function () use ($user, $item, $balance) {
            // 1. Kurangi saldo di tabel Ledger
            PointLedger::create([
                'user_id' => $user->id,
                'transaction_type' => 'SPEND',
                'amount' => -$item->point_cost,
                'current_balance' => $balance - $item->point_cost,
                'description' => 'Pembelian item: ' . $item->item_name
            ]);

            // 2. Berikan "Token" reward ke inventori siswa agar bisa dipakai nanti
            UserToken::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'status' => 'AVAILABLE'
            ]);
        });

        return back()->with('success', 'Berhasil menukarkan poin dengan ' . $item->item_name);
    }

    /**
     * Mengembalikan poin siswa ke kondisi sebelum hari ini (menghapus semua mutasi poin hari ini).
     * Digunakan sebagai penanganan bug atau kesalahan massal (contoh: sinkronisasi alfa salah target).
     */
    public function resetPointsToday(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = $request->user_ids;

        DB::transaction(function () use ($userIds) {
            // Hapus semua ledger yang dibuat HARI INI
            // Karena poin saat ini diambil dari latest() ledger, 
            // menghapus riwayat hari ini otomatis mengembalikan saldo poin siswa ke kondisi kemarin.
            PointLedger::whereIn('user_id', $userIds)
                ->whereDate('created_at', now()->toDateString())
                ->delete();
        });

        return back()->with('success', 'Berhasil membatalkan/mereset semua perubahan poin hari ini untuk ' . count($userIds) . ' siswa.');
    }
}
