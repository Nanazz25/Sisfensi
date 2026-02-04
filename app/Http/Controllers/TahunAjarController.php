<?php

namespace App\Http\Controllers;

use App\Models\TahunAjar;
use Illuminate\Http\Request;

class TahunAjarController extends Controller
{
    public function index(Request $request)
    {
        $query = TahunAjar::query();

        if ($request->filled('q')) {
            $query->where('nama', 'like', '%' . $request->q . '%');
        }

        $sort = $request->get('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $tahunAjars = $query->paginate(10)->withQueryString();

        return view('tahun_ajar.index', compact('tahunAjars'));
    }

    public function create()
    {
        return view('tahun_ajar.form', [
            'tahunAjar' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'nullable|boolean'
        ]);

        // pastikan cuma satu aktif
        if ($request->is_active) {
            TahunAjar::where('is_active', true)->update(['is_active' => false]);
        }

        TahunAjar::create([
            'nama' => $request->nama,
            'semester' => $request->semester,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('tahun-ajar.index')
            ->with('success', 'Tahun ajar berhasil ditambahkan');
    }

    public function edit(TahunAjar $tahunAjar)
    {
        return view('tahun_ajar.form', compact('tahunAjar'));
    }

    public function update(Request $request, TahunAjar $tahunAjar)
    {
        $request->validate([
            'nama' => 'required',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'nullable|boolean'
        ]);

        if ($request->is_active) {
            TahunAjar::where('id', '!=', $tahunAjar->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $tahunAjar->update([
            'nama' => $request->nama,
            'semester' => $request->semester,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('tahun-ajar.index')
            ->with('success', 'Tahun ajar berhasil diperbarui');
    }

    public function destroy(TahunAjar $tahunAjar)
    {
        $tahunAjar->delete();
        return back()->with('success', 'Tahun ajar berhasil dihapus');
    }
}
