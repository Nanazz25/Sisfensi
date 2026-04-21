<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRule extends Model
{
    protected $fillable = [
        'rule_name',
        'target_role',
        'trigger_type',
        'attendance_type',
        'basis_type',
        'reference_key',
        'offset_minutes',
        'condition_operator',
        'condition_value',
        'point_modifier',
    ];
}
