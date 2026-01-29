<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLocation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function attendanceLocations()
    {
        return $this->hasMany(AttendanceLocation::class);
    }
}
