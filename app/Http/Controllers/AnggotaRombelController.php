<?php

namespace App\Http\Controllers;

use App\Models\AnggotaRombel;
use App\Models\RombonganBelajar;
use App\Models\PesertaDidik;
use App\Models\TahunAjar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnggotaRombelController extends Controller
{
    private function tahunAjarAktif()
    {
        return TahunAjar::where('is_active', true)->first();
    }

    public function getSiswaByRombel(RombonganBelajar $rombel)
    {
        $tahunAjarId = $rombel->tahun_ajar_id;

        $siswa = PesertaDidik::whereDoesntHave('anggotaRombel.rombonganBelajar', function ($q) use ($tahunAjarId) {
            $q->where('tahun_ajar_id', $tahunAjarId);
        })
            ->with('user:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->user->name,
                    'nis' => $item->nis,
                ];
            });

        return response()->json($siswa);
    }

    public function store(Request $request, RombonganBelajar $rombel)
    {
        $request->validate([
            'peserta_didik_id' => 'required|exists:peserta_didik,id',
        ]);

        $exists = AnggotaRombel::where('peserta_didik_id', $request->peserta_didik_id)
            ->whereHas('rombonganBelajar', function ($q) use ($rombel) {
                $q->where('tahun_ajar_id', $rombel->tahun_ajar_id);
            })
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'peserta_didik_id' =>
                    'Siswa sudah terdaftar di rombel lain pada tahun ajaran yang sama.'
            ]);
        }

        AnggotaRombel::create([
            'rombongan_belajar_id' => $rombel->id,
            'peserta_didik_id' => $request->peserta_didik_id,
        ]);

        return back()->with('success', 'Siswa berhasil dimasukkan ke rombel');
    }

    public function destroy(RombonganBelajar $rombel, AnggotaRombel $anggotaRombel)
    {
        if ($anggotaRombel->rombongan_belajar_id !== $rombel->id) {
            abort(403);
        }

        $anggotaRombel->delete();

        return back()->with('success', 'Siswa dikeluarkan dari rombel');
    }

}
