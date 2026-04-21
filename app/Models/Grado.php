<?php
// app/Models/Grado.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    use SoftDeletes;
    
    protected $table = 'grados';
    
    protected $fillable = [
        'nivel_id',
        'nombre',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'nivel_id' => 'integer',
        'orden' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Relaciones
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }
    
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'grado_id');
    }
    
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'grado_id');
    }
    
    public function competencias(): HasMany
    {
        return $this->hasMany(Competencia::class, 'grado_id');
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeByNivel($query, $nivelId)
    {
        return $query->where('nivel_id', $nivelId);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }
    
    // Accessors
    public function getNombreCompletoAttribute(): string
    {
        return $this->nivel ? "{$this->nivel->nombre} - {$this->nombre}" : $this->nombre;
    }
    
    public function getNombreUpperAttribute(): string
    {
        return strtoupper($this->nombre);
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
    
    public function activar(): void
    {
        $this->update(['activo' => true]);
    }
    
    public function desactivar(): void
    {
        $this->update(['activo' => false]);
    }
    
    public function perteneceANivel(int $nivelId): bool
    {
        return $this->nivel_id === $nivelId;
    }
}