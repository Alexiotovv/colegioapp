<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'docente_id',
        'alumno_id',
        'apoderado_id',
        'userable_type',
        'userable_id',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ultimo_acceso' => 'datetime',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }
    
    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }
    
    public function apoderado()
    {
        return $this->belongsTo(Apoderado::class);
    }

    // Helper methods
    public function hasRole($roleName): bool
    {
        return $this->role && $this->role->nombre === $roleName;
    }
    
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
    
    public function isDirector(): bool
    {
        return $this->hasRole('director');
    }
    
    public function isDocente(): bool
    {
        return $this->hasRole('docente');
    }
    
    public function isApoderado(): bool
    {
        return $this->hasRole('apoderado');
    }

    public function isTutor(): bool
    {
        return $this->role && $this->role->nombre === 'tutor';
    }
}