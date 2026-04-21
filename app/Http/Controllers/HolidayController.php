<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->id() !== 1) {
            abort(403, 'Hanya Super Admin yang dapat mengelola hari libur.');
        }

        $query = Holiday::query();

        // 🔍 SEARCH
        if ($request->filled('q')) {
            $query->where('description', 'like', '%' . $request->q . '%');
        }

        // ⬆⬇ SORT
        $sort = $request->get('sort', 'desc');
        $holidays = $query->orderBy('date', $sort)->paginate(10)->withQueryString();
        
        // All holidays for calendar visualization
        $allHolidays = Holiday::all();
        
        // Settings for calendar logic
        $schoolDaysStr = \App\Models\SchoolSetting::where('key', 'hari_sekolah')->first()->value ?? 'senin,selasa,rabu,kamis,jumat';

        return view('holidays.index', compact('holidays', 'allHolidays', 'schoolDaysStr'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'description' => 'required|string|max:255',
        ]);

        Holiday::create($request->all());

        return back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'description' => 'required|string|max:255',
        ]);

        $holiday->update($request->all());

        return back()->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return back()->with('success', 'Hari libur berhasil dihapus.');
    }
}
