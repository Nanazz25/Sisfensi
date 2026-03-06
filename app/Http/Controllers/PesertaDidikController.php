<?php

namespace App\Http\Controllers;

use App\Models\PesertaDidik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PesertaDidikController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;
        $sort = $request->sort === 'asc' ? 'asc' : 'desc';

        $siswa = PesertaDidik::with('user')
            ->when($q, function ($query) use ($q) {
                $query->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhere('no_induk', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%");
            })
            ->orderBy('created_at', $sort)
            ->paginate(10)
            ->withQueryString();

        return view('peserta-didik.index', compact('siswa'));
    }

    public function show(PesertaDidik $pesertaDidik)
    {
        $user = auth()->user();

        // Security Check for Guru
        if ($user->role === 'guru') {
            $isWalasForThisStudent = $pesertaDidik->anggotaRombel()
                ->whereHas('rombonganBelajar', function ($q) use ($user) {
                    $q->where('wali_kelas_id', $user->teacher->id ?? 0);
                })->exists();

            if (!$isWalasForThisStudent) {
                abort(403, 'Anda hanya dapat melihat detail siswa di kelas perwalian Anda sendiri.');
            }
        }

        $pesertaDidik->load('user');

        return view('peserta-didik.show', compact('pesertaDidik'));
    }

    public function showPhoto(PesertaDidik $pesertaDidik)
    {
        if (!$pesertaDidik->foto_wajah || !Storage::exists($pesertaDidik->foto_wajah)) {
            abort(404);
        }

        $encrypted = Storage::get($pesertaDidik->foto_wajah);

        try {
            $decrypted = Crypt::decrypt($encrypted);

            return response($decrypted)->header('Content-Type', 'image/jpeg');
        } catch (\Exception $e) {
            abort(500, 'Gagal mendekripsi foto');
        }
    }

    public function create()
    {
        $users = User::where('role', 'siswa')
            ->whereDoesntHave('pesertaDidik')
            ->get();

        return view('peserta-didik.form', [
            'siswa' => null,
            'users' => $users,
            'nis' => $this->generateNis()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nama_lengkap' => 'required',
            'no_induk' => 'required|unique:peserta_didik,no_induk',
            'nisn' => 'required|unique:peserta_didik,nisn',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        PesertaDidik::create([
            'user_id' => $request->user_id,
            'nama_lengkap' => $request->nama_lengkap,
            'no_induk' => $request->no_induk,
            'nisn' => $request->nisn,
            'nik' => $request->nik,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        return redirect()
            ->route('peserta-didik.index')
            ->with('success', 'Peserta didik berhasil ditambahkan');
    }

    public function edit(PesertaDidik $pesertaDidik)
    {
        return view('peserta-didik.form', [
            'siswa' => $pesertaDidik,
            'users' => collect(),
            'nis' => $pesertaDidik->nis
        ]);
    }

    public function update(Request $request, PesertaDidik $pesertaDidik)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'no_induk' => 'required|unique:peserta_didik,no_induk,' . $pesertaDidik->id,
            'nisn' => 'required|unique:peserta_didik,nisn,' . $pesertaDidik->id,
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        $pesertaDidik->update($request->only([
            'nama_lengkap',
            'no_induk',
            'nisn',
            'nik',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
        ]));

        return redirect()
            ->route('peserta-didik.index')
            ->with('success', 'Data peserta didik diperbarui');
    }

    public function destroy(PesertaDidik $pesertaDidik)
    {
        $pesertaDidik->delete();

        return back()->with('success', 'Peserta didik berhasil dihapus');
    }

    private function generateNis()
    {
        $last = PesertaDidik::orderBy('id', 'desc')->first();

        $number = $last
            ? intval(substr($last->nis, -5)) + 1
            : 1;

        return now()->year . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
