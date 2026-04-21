<?php
// app/Models/Periodo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periodo extends Model
{
    
    
    protected $table = 'periodos';
    
    protected $fillable = [
        'anio_academico_id',
        'nombre',
        'orden',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];
    
    protected $casts = [
        'anio_academico_id' => 'integer',
        'orden' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Constantes
    const BIMESTRES = [
        1 => 'I Bimestre',
        2 => 'II Bimestre',
        3 => 'III Bimestre',
        4 => 'IV Bimestre',
    ];
    
    // Relaciones
    public function anioAcademico(): BelongsTo
    {
        return $this->belongsTo(AnioAcademico::class, 'anio_academico_id');
    }
    
    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'periodo_id');
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeByAnio($query, $anioId)
    {
        return $query->where('anio_academico_id', $anioId);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden');
    }
    
    // Accessors
    public function getNombreCompletoAttribute(): string
    {
        $anio = $this->anioAcademico ? $this->anioAcademico->anio : '';
        return "{$this->nombre} - {$anio}";
    }
    
    public function getRangoFechasAttribute(): string
    {
        return $this->fecha_inicio->format('d/m/Y') . ' al ' . $this->fecha_fin->format('d/m/Y');
    }
    
    public function getEstadoAttribute(): string
    {
        $hoy = now();
        if (!$this->activo) return 'Inactivo';
        if ($hoy->lt($this->fecha_inicio)) return 'Próximo';
        if ($hoy->between($this->fecha_inicio, $this->fecha_fin)) return 'En curso';
        return 'Finalizado';
    }
    
    public function getEstadoBadgeAttribute(): string
    {
        $estado = $this->estado;
        switch($estado) {
            case 'En curso': return '<span class="badge bg-success">En curso</span>';
            case 'Próximo': return '<span class="badge bg-info">Próximo</span>';
            case 'Finalizado': return '<span class="badge bg-secondary">Finalizado</span>';
            default: return '<span class="badge bg-danger">Inactivo</span>';
        }
    }
    
    public function getDuracionDiasAttribute(): int
    {
        return $this->fecha_inicio->diffInDays($this->fecha_fin);
    }
    
    public function getDiasTranscurridosAttribute(): int
    {
        $hoy = now();
        if ($hoy->lt($this->fecha_inicio)) return 0;
        if ($hoy->gt($this->fecha_fin)) return $this->duracionDias;
        return $this->fecha_inicio->diffInDays($hoy);
    }
    
    public function getPorcentajeAvanceAttribute(): float
    {
        $total = $this->duracionDias;
        if ($total <= 0) return 0;
        return round(($this->diasTranscurridos / $total) * 100);
    }
    
    // Helper methods
    public function isActivo(): bool
    {
        return $this->activo;
    }
    
    public function isEnCurso(): bool
    {
        $hoy = now();
        return $this->activo && $hoy->between($this->fecha_inicio, $this->fecha_fin);
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