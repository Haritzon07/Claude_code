<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'time_block_id',
        'day_of_week',
        'subject_id',
        'teacher_id',
        'classroom_id',
        'notes',
    ];

    // Relaciones
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function timeBlock()
    {
        return $this->belongsTo(TimeBlock::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Scopes
    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeByTimeBlock($query, $timeBlockId)
    {
        return $query->where('time_block_id', $timeBlockId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // Métodos auxiliares
    public function hasConflict()
    {
        // Verificar conflictos de docente
        if ($this->teacher_id) {
            $teacherConflict = self::where('teacher_id', $this->teacher_id)
                ->where('day_of_week', $this->day_of_week)
                ->where('time_block_id', $this->time_block_id)
                ->where('id', '!=', $this->id)
                ->exists();

            if ($teacherConflict) {
                return ['type' => 'teacher', 'message' => 'Conflicto de docente'];
            }
        }

        // Verificar conflictos de salón
        if ($this->classroom_id) {
            $classroomConflict = self::where('classroom_id', $this->classroom_id)
                ->where('day_of_week', $this->day_of_week)
                ->where('time_block_id', $this->time_block_id)
                ->where('id', '!=', $this->id)
                ->exists();

            if ($classroomConflict) {
                return ['type' => 'classroom', 'message' => 'Conflicto de salón'];
            }
        }

        return false;
    }
}
