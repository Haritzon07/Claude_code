<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'grade',
        'weekly_hours',
        'is_required',
    ];

    protected $casts = [
        'grade' => 'integer',
        'weekly_hours' => 'integer',
        'is_required' => 'boolean',
    ];

    // Relaciones
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
