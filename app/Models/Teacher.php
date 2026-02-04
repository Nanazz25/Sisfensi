<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nip',
        'nuptk',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rombonganBelajar()
    {
        return $this->hasMany(RombonganBelajar::class, 'wali_kelas_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
