<?php
// app/Models/CargaHoraria.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargaHoraria extends Model
{
    use SoftDeletes;
    
    protected $table = 'carga_horaria';
    
    protected $fillable = [
        'docente_id',
        'curso_id',
        'aula_id',
        'horas_semanales',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'estado',
        'observaciones'
    ];
    
    protected $casts = [
        'horas_semanales' => 'integer',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Constantes
    const DIAS_SEMANA = [
        'LUNES' => 'Lunes',
        'MARTES' => 'Martes',
        'MIERCOLES' => 'Miércoles',
        'JUEVES' => 'Jueves',
        'VIERNES' => 'Viernes',
        'SABADO' => 'Sábado',
    ];
    
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';
    
    const ESTADOS = [
        self::ESTADO_ACTIVO => 'Activo',
        self::ESTADO_INACTIVO => 'Inactivo',
    ];
    
    // 🔥 Relación con User (docente)
    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
    
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }
    
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }
    
    // Accessors
    public function getDiaSemanaNombreAttribute(): string
    {
        return self::DIAS_SEMANA[$this->dia_semana] ?? $this->dia_semana;
    }
    
    public function getHorarioAttribute(): string
    {
        if ($this->hora_inicio && $this->hora_fin) {
            return \Carbon\Carbon::parse($this->hora_inicio)->format('H:i') . ' - ' . \Carbon\Carbon::parse($this->hora_fin)->format('H:i');
        }
        return 'Horario no definido';
    }
    
    public function getEstadoBadgeAttribute(): string
    {
        if ($this->estado === 'activo') {
            return '<span class="badge bg-success">Activo</span>';
        }
        return '<span class="badge bg-secondary">Inactivo</span>';
    }
    
    // Helper methods
    public function isActivo(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }
    
    public function activar(): void
    {
        $this->update(['estado' => self::ESTADO_ACTIVO]);
    }
    
    public function desactivar(): void
    {
        $this->update(['estado' => self::ESTADO_INACTIVO]);
    }
}