<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendancePermission extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function anggotaRombel()
    {
        return $this->belongsTo(AnggotaRombel::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
