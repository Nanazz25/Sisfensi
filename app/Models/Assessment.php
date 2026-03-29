<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluator_id',
        'evaluatee_id',
        'assessment_date',
        'period',
        'general_notes',
    ];

    protected $casts = [
        'assessment_date' => 'date',
    ];


    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function evaluatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(AssessmentDetail::class, 'assessment_id');
    }
}
