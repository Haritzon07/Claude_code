<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'order',
        'type',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function scheduleBlocks()
    {
        return $this->hasMany(ScheduleBlock::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }

    public function scopeClassBlocks($query)
    {
        return $query->where('type', 'clase');
    }

    public function scopeBreakBlocks($query)
    {
        return $query->whereIn('type', ['recreo', 'almuerzo', 'descanso']);
    }
}
