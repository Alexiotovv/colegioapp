<?php
// app/Models/Matricula.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Matricula extends Model
{
    // use SoftDeletes;
    
    protected $table = 'matriculas';
    
    protected $fillable = [
        'alumno_id',
        'apoderado_id',
        'aula_id',
        'fecha_matricula',
        'estado',
        'observaciones'
    ];
    
    protected $casts = [
        'fecha_matricula' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // 'deleted_at' => 'datetime',
    ];
    
    // Constantes
    const ESTADO_ACTIVA = 'activa';
    const ESTADO_RETIRADA = 'retirada';
    const ESTADO_CULMINADA = 'culminada';
    
    const ESTADOS = [
        self::ESTADO_ACTIVA => 'Activa',
        self::ESTADO_RETIRADA => 'Retirada',
        self::ESTADO_CULMINADA => 'Culminada',
    ];
    
    // Relaciones
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
    
    public function apoderado(): BelongsTo
    {
        return $this->belongsTo(Apoderado::class, 'apoderado_id');
    }
    
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }
    
    // Acceso a datos a través del aula
    public function getGradoAttribute()
    {
        return $this->aula ? $this->aula->grado : null;
    }
    
    public function getSeccionAttribute()
    {
        return $this->aula ? $this->aula->seccion : null;
    }
    
    public function getAnioAcademicoAttribute()
    {
        return $this->aula ? $this->aula->anioAcademico : null;
    }
    
    // Accessors
    public function getEstadoNombreAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? 'Desconocido';
    }
    
    public function getEstadoBadgeAttribute(): string
    {
        switch($this->estado) {
            case 'activa':
                return '<span class="badge bg-success">Activa</span>';
            case 'retirada':
                return '<span class="badge bg-danger">Retirada</span>';
            case 'culminada':
                return '<span class="badge bg-info">Culminada</span>';
            default:
                return '<span class="badge bg-secondary">Desconocido</span>';
        }
    }
    
    // Helper methods
    public function isActiva(): bool
    {
        return $this->estado === self::ESTADO_ACTIVA;
    }
    
    public function activar(): void
    {
        $this->update(['estado' => self::ESTADO_ACTIVA]);
    }
    
    public function retirar(): void
    {
        $this->update(['estado' => self::ESTADO_RETIRADA]);
    }
    
    public function culminar(): void
    {
        $this->update(['estado' => self::ESTADO_CULMINADA]);
    }
}