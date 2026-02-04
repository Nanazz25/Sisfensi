<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurusan;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::all();
        return view('jurusan.index', compact('jurusans'));
    }

    public function create()
    {
        return view('jurusan.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_jurusan' => 'required|unique:jurusan,kode_jurusan',
            'nama_jurusan' => 'required',
        ]);

        Jurusan::create($request->all());

        return redirect()->route('jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan');
    }

    public function edit(Jurusan $jurusan)
    {
        return view('jurusan.form', compact('jurusan'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'kode_jurusan' => 'required|unique:jurusan,kode_jurusan,' . $jurusan->id,
            'nama_jurusan' => 'required',
        ]);

        $jurusan->update($request->all());

        return redirect()->route('jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui');
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();
        return back()->with('success', 'Jurusan berhasil dihapus');
    }
}
