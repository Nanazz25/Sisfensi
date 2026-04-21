<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlexibilityItem extends Model
{
    protected $fillable = [
        'item_name',
        'description',
        'point_cost',
        'item_type',
        'effect_value',
        'purchase_limit',
        'purchase_period',
        'stock_limit',
    ];
}
