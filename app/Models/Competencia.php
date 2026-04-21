<?php
// app/Models/Competencia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competencia extends Model
{
    protected $table = 'competencias';
    
    protected $fillable = [
        'curso_id',
        'nombre',
        'descripcion',
        'ponderacion',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'ponderacion' => 'decimal:2',
        'orden' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relaciones
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }
    
    public function capacidades(): HasMany
    {
        return $this->hasMany(Capacidad::class, 'competencia_id');
    }
    
    // Acceso al grado a través del curso
    public function getGradoAttribute()
    {
        return $this->curso ? $this->curso->grado : null;
    }
    
    public function getNivelAttribute()
    {
        return $this->grado ? $this->grado->nivel : null;
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeByCurso($query, $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }
    
    // Accessors
    public function getNombreCompletoAttribute(): string
    {
        $curso = $this->curso ? $this->curso->nombre : '';
        return "{$curso} - {$this->nombre}";
    }
    
    public function getCursoInfoAttribute(): string
    {
        if (!$this->curso) return 'N/A';
        $grado = $this->curso->grado;
        $nivel = $grado ? $grado->nivel : null;
        return ($nivel ? $nivel->nombre . ' - ' : '') . ($grado ? $grado->nombre . ' - ' : '') . $this->curso->nombre;
    }
    
    // Helper methods
    public function isActivo(): bool
    {
        return $this->activo;
    }
    
    public function activar(): void
    {
        $this->update(['activo' => true]);
    }
    
    public function desactivar(): void
    {
        $this->update(['activo' => false]);
    }
}