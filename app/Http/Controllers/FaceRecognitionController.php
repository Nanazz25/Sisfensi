<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\PesertaDidik;
use App\Models\User;

class FaceRecognitionController extends Controller
{
    /**
     * Tampilkan halaman pendaftaran wajah (Enrollment)
     */
    public function indexEnroll()
    {
        // Admin/Guru mendaftarkan siswa
        $students = PesertaDidik::with('user')->get()->sortBy('user.name');
        return view('face.enroll', compact('students'));
    }

    /**
     * Simpan data wajah baru (Enrollment)
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'face_embedding' => 'required|array',
            'peserta_didik_id' => 'required|exists:peserta_didik,id'
        ]);

        $peserta = PesertaDidik::findOrFail($request->peserta_didik_id);

        $base64 = $request->image;
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = base64_decode($image);

        // Proses gambar
        $img = Image::make($image)
            ->resize(300, 300)
            ->encode('jpg', 60);

        // Enkripsi file demi privasi data biometrik
        // Gunakan ID user siswa untuk penamaan file agar unik
        $encrypted = Crypt::encrypt((string) $img);
        $filename = 'faces/' . $peserta->user_id . '_' . time() . '.enc';

        // Hapus foto lama jika ada
        if ($peserta->foto_wajah && Storage::exists($peserta->foto_wajah)) {
            Storage::delete($peserta->foto_wajah);
        }

        Storage::put($filename, $encrypted);

        $peserta->update([
            'foto_wajah' => $filename,
            'face_embedding' => json_encode($request->face_embedding),
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Data wajah ' . $peserta->user->name . ' berhasil didaftarkan'
        ]);
    }
}
