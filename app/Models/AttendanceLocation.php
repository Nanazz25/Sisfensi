<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLocation extends Model
{
    use HasFactory;

    protected $table = 'attendance_locations';
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'lokasi_valid' => 'boolean',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function schoolLocation()
    {
        return $this->belongsTo(SchoolLocation::class);
    }
}
