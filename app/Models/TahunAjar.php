<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjar extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajar';
    protected $guarded = ['id'];

    protected $fillable = [
        'nama',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    public function rombonganBelajar()
    {
        return $this->hasMany(RombonganBelajar::class);
    }
}
