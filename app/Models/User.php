<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'identification',
        'phone',
        'user_type',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
    ];

    // Relaciones
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function createdActivities()
    {
        return $this->hasMany(Activity::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    // Métodos auxiliares
    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function isCoordinator()
    {
        return $this->user_type === 'coordinador';
    }

    public function isTeacher()
    {
        return $this->user_type === 'docente';
    }

    public function isStudent()
    {
        return $this->user_type === 'estudiante';
    }

    public function isParent()
    {
        return $this->user_type === 'padre';
    }
}
