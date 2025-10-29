<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'color',
        'weekly_hours',
        'is_active',
    ];

    protected $casts = [
        'weekly_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subjects')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function gradeSubjects()
    {
        return $this->hasMany(GradeSubject::class);
    }

    public function scheduleBlocks()
    {
        return $this->hasMany(ScheduleBlock::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForGrade($query, $grade)
    {
        return $query->whereHas('gradeSubjects', function($q) use ($grade) {
            $q->where('grade', $grade);
        });
    }
}
