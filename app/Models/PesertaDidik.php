<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaDidik extends Model
{
    use HasFactory;

    protected $table = 'peserta_didik';
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'no_induk',
        'nisn',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'foto_wajah',
        'face_embedding'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anggotaRombel()
    {
        return $this->hasMany(AnggotaRombel::class);
    }

    public function faceLogs()
    {
        return $this->hasMany(FaceLog::class);
    }
}
