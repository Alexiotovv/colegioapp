<?php
// app/Models/Nivel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nivel extends Model
{
    use SoftDeletes;
    
    protected $table = 'niveles';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // 🔥 RELACIÓN CON CURSOS - AGREGAR ESTO
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'nivel_id');
    }
    
    // Relaciones existentes
    public function grados(): HasMany
    {
        return $this->hasMany(Grado::class, 'nivel_id');
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }
    
    // Accessors
    public function getNombreUpperAttribute(): string
    {
        return strtoupper($this->nombre);
    }
    
    public function getCantidadGradosAttribute(): int
    {
        return $this->grados()->count();
    }
    
    public function getCantidadCursosAttribute(): int
    {
        return $this->cursos()->count();
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
    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'nivel_id');
    }
    public function tiposInasistencia()
    {
        return $this->hasMany(TipoInasistencia::class, 'nivel_id');
    }
    
    public function tiposOtrasEvaluaciones()
    {
        return $this->hasMany(TipoOtraEvaluacion::class, 'nivel_id');
    }
    public function competenciasTransversales()
    {
        return $this->hasMany(CompetenciaTransversal::class, 'nivel_id');
    }

    public function tiposOrdenMerito()
    {
        return $this->hasMany(TipoOrdenMerito::class, 'nivel_id');
    }

    public function evaluacionesActitudinales()
    {
        return $this->hasMany(\App\Models\EvaluacionActitudinal::class, 'nivel_id');
    }
}