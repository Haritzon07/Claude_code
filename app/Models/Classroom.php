<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'capacity',
        'building',
        'floor',
        'equipment',
        'is_available',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_available' => 'boolean',
    ];

    // Relaciones
    public function scheduleBlocks()
    {
        return $this->hasMany(ScheduleBlock::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Métodos auxiliares
    public function isAvailableAt($dayOfWeek, $timeBlockId, $scheduleId = null)
    {
        $query = $this->scheduleBlocks()
            ->where('day_of_week', $dayOfWeek)
            ->where('time_block_id', $timeBlockId);

        if ($scheduleId) {
            $query->where('schedule_id', '!=', $scheduleId);
        }

        return $query->count() === 0;
    }
}
