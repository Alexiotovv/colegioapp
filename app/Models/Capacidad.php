<?php
// app/Models/Capacidad.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Capacidad extends Model
{
    protected $table = 'capacidades';
    
    protected $fillable = [
        'competencia_id',
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
    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'competencia_id');
    }
    
    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'capacidad_id');
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeByCompetencia($query, $competenciaId)
    {
        return $query->where('competencia_id', $competenciaId);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }
    
    // Accessors
    public function getNombreCompletoAttribute(): string
    {
        $competencia = $this->competencia ? $this->competencia->nombre : '';
        return "{$competencia} - {$this->nombre}";
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