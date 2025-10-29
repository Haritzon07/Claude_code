<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'title',
        'description',
        'type',
        'start_datetime',
        'end_datetime',
        'all_day',
        'location',
        'classroom_id',
        'participants',
        'color',
        'is_public',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'all_day' => 'boolean',
        'participants' => 'array',
        'is_public' => 'boolean',
    ];

    // Relaciones
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>=', now())->orderBy('start_datetime');
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_datetime', [$startDate, $endDate])
              ->orWhereBetween('end_datetime', [$startDate, $endDate])
              ->orWhere(function($q2) use ($startDate, $endDate) {
                  $q2->where('start_datetime', '<=', $startDate)
                     ->where('end_datetime', '>=', $endDate);
              });
        });
    }

    // Métodos auxiliares
    public function isForGrade($grade)
    {
        if (!$this->participants) {
            return true; // Si no hay participantes específicos, es para todos
        }

        $grades = $this->participants['grades'] ?? [];
        return in_array($grade, $grades);
    }

    public function isForTeacher($teacherId)
    {
        if (!$this->participants) {
            return true;
        }

        $teachers = $this->participants['teachers'] ?? [];
        return in_array($teacherId, $teachers);
    }
}
