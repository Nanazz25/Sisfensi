<?php

namespace App\Http\Controllers;

use App\Models\SchoolLocation;
use Illuminate\Http\Request;

class SchoolLocationController extends Controller
{
    public function index()
    {
        $locations = SchoolLocation::orderBy('created_at', 'desc')->get();
        return view('school-locations.index', compact('locations'));
    }

    public function create()
    {
        return view('school-locations.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_maks' => 'required|integer|min:1',
        ]);

        SchoolLocation::create($request->all());

        return redirect()
            ->route('school-locations.index')
            ->with('success', 'Lokasi sekolah berhasil ditambahkan');
    }

    public function edit(SchoolLocation $schoolLocation)
    {
        return view('school-locations.form', compact('schoolLocation'));
    }

    public function update(Request $request, SchoolLocation $schoolLocation)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_maks' => 'required|integer|min:1',
        ]);

        $schoolLocation->update($request->all());

        return redirect()
            ->route('school-locations.index')
            ->with('success', 'Lokasi sekolah berhasil diperbarui');
    }

    public function destroy(SchoolLocation $schoolLocation)
    {
        $schoolLocation->delete();

        return back()->with('success', 'Lokasi sekolah berhasil dihapus');
    }
}
