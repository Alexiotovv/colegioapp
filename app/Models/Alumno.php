<?php
// app/Models/Alumno.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Alumno extends Model
{
    use SoftDeletes;
    
    protected $table = 'alumnos';
    
    protected $fillable = [
        'codigo_estudiante',
        'dni',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'telefono',
        'email',
        'estado',
        'observaciones'
    ];
    
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'estado' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Constantes
    const SEXO_MASCULINO = 'M';
    const SEXO_FEMENINO = 'F';
    
    const SEXOS = [
        self::SEXO_MASCULINO => 'Masculino',
        self::SEXO_FEMENINO => 'Femenino',
    ];
    
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';
    const ESTADO_RETIRADO = 'retirado';
    const ESTADO_EGRESADO = 'egresado';
    
    const ESTADOS = [
        self::ESTADO_ACTIVO => 'Activo',
        self::ESTADO_INACTIVO => 'Inactivo',
        self::ESTADO_RETIRADO => 'Retirado',
        self::ESTADO_EGRESADO => 'Egresado',
    ];
    
    // Relaciones
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'alumno_id');
    }
    
    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'alumno_id');
    }
    
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'alumno_id');
    }
    
    public function apoderados(): BelongsToMany
    {
        return $this->belongsToMany(Apoderado::class, 'alumno_apoderado', 'alumno_id', 'apoderado_id')
                    ->withPivot('parentesco', 'recibe_notificaciones', 'es_tutor', 'puede_retirar')
                    ->withTimestamps();
    }
    
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }
    
    // 🔥 MÉTODO CORREGIDO - Usando aula en lugar de anio_academico_id
    public function getMatriculaActivaAttribute()
    {
        $anioActivo = AnioAcademico::where('activo', true)->first();
        if (!$anioActivo) return null;
        
        return $this->matriculas()
            ->whereHas('aula', function($query) use ($anioActivo) {
                $query->where('anio_academico_id', $anioActivo->id);
            })
            ->where('estado', 'activa')
            ->first();
    }
    
    // 🔥 MÉTODO CORREGIDO - Obtener grado actual a través del aula
    public function getGradoActualAttribute()
    {
        $matriculaActiva = $this->matriculaActiva;
        return $matriculaActiva ? $matriculaActiva->aula->grado : null;
    }
    
    // 🔥 MÉTODO CORREGIDO - Obtener sección actual a través del aula
    public function getSeccionActualAttribute()
    {
        $matriculaActiva = $this->matriculaActiva;
        return $matriculaActiva ? $matriculaActiva->aula->seccion : null;
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }
    
    public function scopeByEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nombres', 'LIKE', "%{$search}%")
              ->orWhere('apellido_paterno', 'LIKE', "%{$search}%")
              ->orWhere('apellido_materno', 'LIKE', "%{$search}%")
              ->orWhere('dni', 'LIKE', "%{$search}%")
              ->orWhere('codigo_estudiante', 'LIKE', "%{$search}%");
        });
    }
    
    // Accessors
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellido_paterno} {$this->apellido_materno}, {$this->nombres}";
    }
    
    public function getNombreCompletoInversoAttribute(): string
    {
        return "{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}";
    }
    
    public function getSexoNombreAttribute(): string
    {
        return self::SEXOS[$this->sexo] ?? 'No especificado';
    }
    
    public function getEstadoNombreAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? 'Desconocido';
    }
    
    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
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
    
    public function retirar(): void
    {
        $this->update(['estado' => self::ESTADO_RETIRADO]);
    }
    
    public function egresar(): void
    {
        $this->update(['estado' => self::ESTADO_EGRESADO]);
    }
    
    // Generar código de estudiante automático
    public static function generarCodigoEstudiante(): string
    {
        $year = date('Y');
        $last = self::whereYear('created_at', $year)->count();
        $numero = str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        return "{$year}{$numero}";
    }
}