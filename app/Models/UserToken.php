<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'status',
        'used_at_attendance_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(FlexibilityItem::class, 'item_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'used_at_attendance_id');
    }
}
