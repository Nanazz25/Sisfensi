<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class SchoolSettingController extends Controller
{
    public function index()
    {
        $settings = SchoolSetting::all();
        return view('school-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            SchoolSetting::where('key', $key)->update(['value' => $value]);
        }

        foreach ($data as $key => $value) {
            SchoolSetting::where('key', $key)->update(['value' => $value]);
        }
        return back()->with('success', 'Pengaturan sekolah berhasil diperbarui.');
    }
}
