<?php
// app/Models/Seccion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seccion extends Model
{
    
    protected $table = 'secciones';
    
    protected $fillable = [
        'nombre',
        'turno',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Constantes para turnos
    const TURNO_MAÑANA = 'MAÑANA';
    const TURNO_TARDE = 'TARDE';
    const TURNO_NOCHE = 'NOCHE';
    
    const TURNOS = [
        self::TURNO_MAÑANA => 'Mañana',
        self::TURNO_TARDE => 'Tarde',
        self::TURNO_NOCHE => 'Noche',
    ];
    
    // Relaciones
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'seccion_id');
    }
    
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'seccion_id');
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeByTurno($query, $turno)
    {
        return $query->where('turno', $turno);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('nombre');
    }
    
    // Accessors
    public function getNombreUpperAttribute(): string
    {
        return strtoupper($this->nombre);
    }
    
    public function getTurnoNombreAttribute(): string
    {
        return self::TURNOS[$this->turno] ?? $this->turno;
    }
    
    public function getCantidadMatriculasAttribute(): int
    {
        return $this->matriculas()->count();
    }
    
    // Helper methods
    public function isActivo(): bool
    {
        return $this->activo;
    }
    
    public function isTurnoMañana(): bool
    {
        return $this->turno === self::TURNO_MAÑANA;
    }
    
    public function isTurnoTarde(): bool
    {
        return $this->turno === self::TURNO_TARDE;
    }
    
    public function isTurnoNoche(): bool
    {
        return $this->turno === self::TURNO_NOCHE;
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