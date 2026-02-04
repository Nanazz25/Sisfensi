<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedules';
    protected $guarded = ['id'];
    protected $fillable = [
        'rombongan_belajar_id',
        'subject_id',
        'teacher_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    public function rombonganBelajar()
    {
        return $this->belongsTo(RombonganBelajar::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
