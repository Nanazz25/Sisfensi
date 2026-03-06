<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public $timestamps = false;

    protected static function booted()
    {
        static::deleting(function ($faceLog) {
            if ($faceLog->image_path && \Illuminate\Support\Facades\Storage::exists($faceLog->image_path)) {
                \Illuminate\Support\Facades\Storage::delete($faceLog->image_path);
            }
        });
    }

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function pesertaDidik()
    {
        return $this->belongsTo(PesertaDidik::class);
    }
}
