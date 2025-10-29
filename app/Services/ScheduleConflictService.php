<?php

namespace App\Services;

use App\Models\ScheduleBlock;
use App\Models\Teacher;
use App\Models\Classroom;

class ScheduleConflictService
{
    /**
     * Verifica conflictos para un bloque de horario
     */
    public function checkBlockConflicts(array $blockData, $excludeBlockId = null)
    {
        $conflicts = [];

        // Verificar conflicto de docente
        if (isset($blockData['teacher_id']) && $blockData['teacher_id']) {
            $teacherConflict = $this->checkTeacherConflict(
                $blockData['teacher_id'],
                $blockData['day_of_week'],
                $blockData['time_block_id'],
                $excludeBlockId
            );

            if ($teacherConflict) {
                $conflicts[] = $teacherConflict;
            }
        }

        // Verificar conflicto de salón
        if (isset($blockData['classroom_id']) && $blockData['classroom_id']) {
            $classroomConflict = $this->checkClassroomConflict(
                $blockData['classroom_id'],
                $blockData['day_of_week'],
                $blockData['time_block_id'],
                $excludeBlockId
            );

            if ($classroomConflict) {
                $conflicts[] = $classroomConflict;
            }
        }

        return $conflicts;
    }

    /**
     * Verifica conflicto de docente
     */
    protected function checkTeacherConflict($teacherId, $dayOfWeek, $timeBlockId, $excludeBlockId = null)
    {
        $query = ScheduleBlock::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('time_block_id', $timeBlockId);

        if ($excludeBlockId) {
            $query->where('id', '!=', $excludeBlockId);
        }

        $conflictingBlock = $query->with(['schedule', 'teacher.user'])->first();

        if ($conflictingBlock) {
            $teacher = Teacher::with('user')->find($teacherId);
            return [
                'type' => 'teacher',
                'message' => "El docente {$teacher->user->name} ya está asignado a otro grupo en este horario (Grado {$conflictingBlock->schedule->grade}-{$conflictingBlock->schedule->group})",
                'conflicting_block_id' => $conflictingBlock->id,
            ];
        }

        return null;
    }

    /**
     * Verifica conflicto de salón
     */
    protected function checkClassroomConflict($classroomId, $dayOfWeek, $timeBlockId, $excludeBlockId = null)
    {
        $query = ScheduleBlock::where('classroom_id', $classroomId)
            ->where('day_of_week', $dayOfWeek)
            ->where('time_block_id', $timeBlockId);

        if ($excludeBlockId) {
            $query->where('id', '!=', $excludeBlockId);
        }

        $conflictingBlock = $query->with(['schedule', 'classroom'])->first();

        if ($conflictingBlock) {
            $classroom = Classroom::find($classroomId);
            return [
                'type' => 'classroom',
                'message' => "El salón {$classroom->name} ya está ocupado por otro grupo en este horario (Grado {$conflictingBlock->schedule->grade}-{$conflictingBlock->schedule->group})",
                'conflicting_block_id' => $conflictingBlock->id,
            ];
        }

        return null;
    }

    /**
     * Obtiene todos los conflictos de un horario completo
     */
    public function getScheduleConflicts($scheduleId)
    {
        $blocks = ScheduleBlock::where('schedule_id', $scheduleId)->get();
        $allConflicts = [];

        foreach ($blocks as $block) {
            $blockData = [
                'teacher_id' => $block->teacher_id,
                'classroom_id' => $block->classroom_id,
                'day_of_week' => $block->day_of_week,
                'time_block_id' => $block->time_block_id,
            ];

            $conflicts = $this->checkBlockConflicts($blockData, $block->id);

            if (!empty($conflicts)) {
                $allConflicts[$block->id] = $conflicts;
            }
        }

        return $allConflicts;
    }

    /**
     * Genera sugerencias automáticas para resolver conflictos
     */
    public function suggestAlternatives($blockData)
    {
        $suggestions = [];

        // Sugerir docentes alternativos
        if (isset($blockData['subject_id'])) {
            $availableTeachers = Teacher::active()
                ->whereHas('subjects', function($q) use ($blockData) {
                    $q->where('subject_id', $blockData['subject_id']);
                })
                ->get()
                ->filter(function($teacher) use ($blockData) {
                    return !$this->checkTeacherConflict(
                        $teacher->id,
                        $blockData['day_of_week'],
                        $blockData['time_block_id']
                    );
                });

            if ($availableTeachers->count() > 0) {
                $suggestions['teachers'] = $availableTeachers;
            }
        }

        // Sugerir salones alternativos
        $availableClassrooms = Classroom::available()
            ->get()
            ->filter(function($classroom) use ($blockData) {
                return !$this->checkClassroomConflict(
                    $classroom->id,
                    $blockData['day_of_week'],
                    $blockData['time_block_id']
                );
            });

        if ($availableClassrooms->count() > 0) {
            $suggestions['classrooms'] = $availableClassrooms;
        }

        return $suggestions;
    }
}
