<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
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
    public function periods()
    {
        return $this->hasMany(AcademicPeriod::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
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
        // Desactivar todos los demás años
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}
