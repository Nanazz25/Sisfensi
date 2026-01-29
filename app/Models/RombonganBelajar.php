<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RombonganBelajar extends Model
{
    use HasFactory;

    protected $table = 'rombongan_belajar';
    protected $guarded = ['id'];

    public function tahunAjar()
    {
        return $this->belongsTo(TahunAjar::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(Teacher::class, 'wali_kelas_id');
    }

    public function anggotaRombel()
    {
        return $this->hasMany(AnggotaRombel::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
