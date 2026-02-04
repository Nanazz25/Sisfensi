<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'jurusan';
    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
    ];

    public function rombel()
    {
        return $this->hasMany(RombonganBelajar::class);
    }
}
