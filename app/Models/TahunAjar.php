<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjar extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajar';
    protected $guarded = ['id'];

    public function rombonganBelajar()
    {
        return $this->hasMany(RombonganBelajar::class);
    }
}
