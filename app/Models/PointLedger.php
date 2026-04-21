<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointLedger extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_type',
        'amount',
        'current_balance',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
