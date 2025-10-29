<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_period_id',
        'grade',
        'group',
        'name',
        'is_active',
    ];

    protected $casts = [
        'grade' => 'integer',
        'group' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
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
        return $query->where('grade', $grade);
    }

    public function scopeForGroup($query, $grade, $group)
    {
        return $query->where('grade', $grade)->where('group', $group);
    }

    // Métodos auxiliares
    public function getBlocksByDay($dayOfWeek)
    {
        return $this->scheduleBlocks()
            ->where('day_of_week', $dayOfWeek)
            ->with(['timeBlock', 'subject', 'teacher', 'classroom'])
            ->orderBy('time_block_id')
            ->get();
    }

    public function detectConflicts()
    {
        $conflicts = [];

        foreach ($this->scheduleBlocks as $block) {
            // Verificar si el docente está ocupado en el mismo horario
            if ($block->teacher_id) {
                $teacherConflict = ScheduleBlock::where('teacher_id', $block->teacher_id)
                    ->where('day_of_week', $block->day_of_week)
                    ->where('time_block_id', $block->time_block_id)
                    ->where('id', '!=', $block->id)
                    ->exists();

                if ($teacherConflict) {
                    $conflicts[] = [
                        'type' => 'teacher',
                        'block_id' => $block->id,
                        'message' => "El docente {$block->teacher->user->name} tiene conflicto de horario"
                    ];
                }
            }

            // Verificar si el salón está ocupado
            if ($block->classroom_id) {
                $classroomConflict = ScheduleBlock::where('classroom_id', $block->classroom_id)
                    ->where('day_of_week', $block->day_of_week)
                    ->where('time_block_id', $block->time_block_id)
                    ->where('id', '!=', $block->id)
                    ->exists();

                if ($classroomConflict) {
                    $conflicts[] = [
                        'type' => 'classroom',
                        'block_id' => $block->id,
                        'message' => "El salón {$block->classroom->name} está ocupado"
                    ];
                }
            }
        }

        return $conflicts;
    }
}
