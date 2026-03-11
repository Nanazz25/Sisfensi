<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use App\Models\PesertaDidik;
use App\Models\AnggotaRombel;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load(['teacher', 'pesertaDidik']);
        $rombel = null;

        if ($user->role === 'siswa' && $user->pesertaDidik) {
            $rombel = AnggotaRombel::with('rombonganBelajar')
                ->where('peserta_didik_id', $user->pesertaDidik->id)
                ->latest()
                ->first();
        } elseif ($user->role === 'guru' && $user->teacher) {
            // Check if they are a walas
            $rombel = \App\Models\RombonganBelajar::where('wali_kelas_id', $user->teacher->id)->first();
        }

        return view('profile.show', compact('user', 'rombel'));
    }

    public function editPassword()
    {
        return view('profile.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = auth()->user();
        $user->update([
            'password' => bcrypt($request->password),
            'password_changed' => true,
        ]);

        return redirect()->route('profile.show')->with('success', 'Password berhasil diperbarui.');
    }
}
