<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\PesertaDidik;

class FaceRecognitionController extends Controller
{
    /**
     * Tampilkan halaman pendaftaran wajah (Enrollment)
     */
    public function indexEnroll()
    {
        return view('face.enroll');
    }

    /**
     * Simpan data wajah baru (Enrollment)
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'face_embedding' => 'required|array'
        ]);

        $base64 = $request->image;
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = base64_decode($image);

        // Proses gambar
        $img = Image::make($image)
            ->resize(300, 300)
            ->encode('jpg', 60);

        // Enkripsi file demi privasi data biometrik
        $encrypted = Crypt::encrypt((string) $img);
        $filename = 'faces/' . auth()->id() . '_' . time() . '.enc';

        Storage::put($filename, $encrypted);

        $peserta = PesertaDidik::where('user_id', auth()->id())->first();

        if (!$peserta) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil peserta didik tidak ditemukan'
            ], 404);
        }

        $peserta->update([
            'foto_wajah' => $filename,
            'face_embedding' => json_encode($request->face_embedding),
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Data wajah berhasil didaftarkan'
        ]);
    }
}
