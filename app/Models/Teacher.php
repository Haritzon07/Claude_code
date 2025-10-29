<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'specialization',
        'max_weekly_hours',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'max_weekly_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects')
            ->withPivot('is_primary')
            ->withTimestamps();
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

    // Métodos auxiliares
    public function getCurrentWeeklyHours($academicPeriodId = null)
    {
        $query = $this->scheduleBlocks();

        if ($academicPeriodId) {
            $query->whereHas('schedule', function($q) use ($academicPeriodId) {
                $q->where('academic_period_id', $academicPeriodId);
            });
        }

        return $query->count();
    }

    public function hasAvailableHours($academicPeriodId = null)
    {
        return $this->getCurrentWeeklyHours($academicPeriodId) < $this->max_weekly_hours;
    }
}
