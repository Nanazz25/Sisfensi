<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaDidik extends Model
{
    use HasFactory;

    protected $table = 'peserta_didik';
    protected $guarded = ['id'];

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
