<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicWeek extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_period_id',
        'week_number',
        'start_date',
        'end_date',
        'is_exam_week',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_exam_week' => 'boolean',
    ];

    // Relaciones
    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    // Scopes
    public function scopeExamWeeks($query)
    {
        return $query->where('is_exam_week', true);
    }
}
