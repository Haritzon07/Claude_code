<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'period_number',
        'name',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function weeks()
    {
        return $this->hasMany(AcademicWeek::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)->first();
    }

    // Métodos auxiliares
    public function activate()
    {
        // Desactivar todos los demás períodos del mismo año
        self::where('academic_year_id', $this->academic_year_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}
