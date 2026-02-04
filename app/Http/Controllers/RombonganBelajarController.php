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

    public function show(RombonganBelajar $rombonganBelajar)
    {
        $anggota = $rombonganBelajar->anggotaRombel()
            ->with('pesertaDidik.user')
            ->get();

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
