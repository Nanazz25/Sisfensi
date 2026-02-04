<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::all();
        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('subjects.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel' => 'required|string|max:255|unique:subjects,nama_mapel',
        ]);

        Subject::create($request->all());

        return redirect()->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan');
    }

    public function edit(Subject $subject)
    {
        return view('subjects.form', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'nama_mapel' => 'required|string|max:255|unique:subjects,nama_mapel,' . $subject->id,
        ]);

        $subject->update($request->all());

        return redirect()->route('subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Mata pelajaran berhasil dihapus');
    }
}
