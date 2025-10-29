<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'grade',
        'template_data',
        'created_by',
        'is_public',
    ];

    protected $casts = [
        'grade' => 'integer',
        'template_data' => 'array',
        'is_public' => 'boolean',
    ];

    // Relaciones
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeForGrade($query, $grade)
    {
        return $query->where(function($q) use ($grade) {
            $q->where('grade', $grade)->orWhereNull('grade');
        });
    }

    // Métodos auxiliares
    public function applyToSchedule(Schedule $schedule)
    {
        if (!$this->template_data) {
            return false;
        }

        foreach ($this->template_data as $blockData) {
            ScheduleBlock::create([
                'schedule_id' => $schedule->id,
                'time_block_id' => $blockData['time_block_id'],
                'day_of_week' => $blockData['day_of_week'],
                'subject_id' => $blockData['subject_id'] ?? null,
                'teacher_id' => $blockData['teacher_id'] ?? null,
                'classroom_id' => $blockData['classroom_id'] ?? null,
                'notes' => $blockData['notes'] ?? null,
            ]);
        }

        return true;
    }
}
