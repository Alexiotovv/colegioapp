<?php
// app/Models/Role.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';
    
    protected $fillable = ['nombre', 'descripcion', 'activo'];
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    // Helper methods
    public function isAdmin(): bool
    {
        return $this->nombre === 'admin';
    }
    
    public function isDirector(): bool
    {
        return $this->nombre === 'director';
    }
    
    public function isDocente(): bool
    {
        return $this->nombre === 'docente';
    }
    
    public function isApoderado(): bool
    {
        return $this->nombre === 'apoderado';
    }
}