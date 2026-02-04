<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PesertaDidik;

class FaceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'peserta_didik_id' => 'required|exists:peserta_didik,id',
            'face_embedding' => 'required|array|size:128',
        ]);

        $siswa = PesertaDidik::findOrFail($request->peserta_didik_id);

        $siswa->face_embedding = json_encode($request->face_embedding);
        $siswa->save();

        return response()->json([
            'status' => 'success'
        ]);
    }
}
