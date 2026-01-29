<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaRombel extends Model
{
    use HasFactory;

    protected $table = 'anggota_rombel';
    protected $guarded = ['id'];
    public $timestamps = false; // Based on migration only having created_at, but Eloquent expects both by default unless disabled or customized. Migration had created_at timestamp.

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function rombonganBelajar()
    {
        return $this->belongsTo(RombonganBelajar::class);
    }

    public function pesertaDidik()
    {
        return $this->belongsTo(PesertaDidik::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
