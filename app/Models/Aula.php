<?php
// app/Models/Aula.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aula extends Model
{
    use SoftDeletes;
    
    protected $table = 'aulas';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'turno',
        'capacidad',
        'ubicacion',
        'activo',
        'nivel_id',
        'grado_id',
        'seccion_id',
        'anio_academico_id',
        'docente_id'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'capacidad' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Constantes
    const TURNO_MAÑANA = 'MAÑANA';
    const TURNO_TARDE = 'TARDE';
    const TURNO_NOCHE = 'NOCHE';
    
    const TURNOS = [
        self::TURNO_MAÑANA => 'Mañana',
        self::TURNO_TARDE => 'Tarde',
        self::TURNO_NOCHE => 'Noche',
    ];
    
    // Relaciones
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }
    
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }
    
    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }
    
    public function anioAcademico(): BelongsTo
    {
        return $this->belongsTo(AnioAcademico::class, 'anio_academico_id');
    }
    
    public function docente(): BelongsTo
    {
           return $this->belongsTo(User::class, 'docente_id');
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
    
    public function scopeByNivel($query, $nivelId)
    {
        return $query->where('nivel_id', $nivelId);
    }
    
    public function scopeByTurno($query, $turno)
    {
        return $query->where('turno', $turno);
    }
    
    // Accessors
    public function getTurnoNombreAttribute(): string
    {
        return self::TURNOS[$this->turno] ?? $this->turno;
    }
    
    public function getNombreCompletoAttribute(): string
    {
        $nivel = $this->nivel ? $this->nivel->nombre : '';
        $grado = $this->grado ? $this->grado->nombre : '';
        $seccion = $this->seccion ? $this->seccion->nombre : '';
        return trim("{$nivel} {$grado} \"{$seccion}\" - {$this->anioAcademico->anio}");
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
    
    // Generar código automático
    public static function generarCodigo($gradoId, $seccionId, $anioId)
    {
        $grado = Grado::find($gradoId);
        $seccion = Seccion::find($seccionId);
        $anio = AnioAcademico::find($anioId);
        
        if (!$grado || !$seccion || !$anio) {
            return 'AULA-' . uniqid();
        }
        
        $codigoBase = strtoupper(substr($grado->nombre, 0, 2) . $seccion->nombre . substr($anio->anio, -2));
        
        // Verificar si el código ya existe
        $contador = 1;
        $codigo = $codigoBase;
        
        while (self::where('codigo', $codigo)->exists()) {
            $codigo = $codigoBase . '-' . $contador;
            $contador++;
        }
        
        return $codigo;
    }
    public function cargaHoraria()
    {
        return $this->hasMany(CargaHoraria::class, 'aula_id');
    }
}