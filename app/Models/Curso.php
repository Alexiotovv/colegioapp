<?php
// app/Models/Curso.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use SoftDeletes;
    
    protected $table = 'cursos';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'horas_semanales',
        'orden',
        'descripcion',
        'activo',
        'nivel_id',
        'anio_academico_id'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'horas_semanales' => 'integer',
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Constantes
    const TIPO_AREA = 'AREA';
    const TIPO_TALLER = 'TALLER';
    const TIPO_TUTORIA = 'TUTORIA';
    
    const TIPOS = [
        self::TIPO_AREA => 'Área Curricular',
        self::TIPO_TALLER => 'Taller',
        self::TIPO_TUTORIA => 'Tutoría',
    ];
    
    // Relaciones
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }
    
    public function anioAcademico(): BelongsTo
    {
        return $this->belongsTo(AnioAcademico::class, 'anio_academico_id');
    }
    
    public function competencias(): HasMany
    {
        return $this->hasMany(Competencia::class, 'curso_id');
    }
    
    // 🔥 Método para obtener grado (puede ser null o calcular desde nivel)
    // Como ya no tenemos grados en cursos, retornamos null o podemos hacer una relación alternativa
    public function getGradoAttribute()
    {
        // Los cursos ahora pertenecen directamente a niveles, no a grados
        return null;
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
    
    public function scopeByAnio($query, $anioId)
    {
        return $query->where('anio_academico_id', $anioId);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }
    
    // Accessors
    public function getTipoNombreAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }
    
    public function getNombreCompletoAttribute(): string
    {
        $nivel = $this->nivel ? $this->nivel->nombre : '';
        return "{$nivel} - {$this->nombre}";
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