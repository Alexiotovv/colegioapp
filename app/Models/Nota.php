<?php
// app/Models/Nota.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Nota extends Model
{
    protected $table = 'notas';
    
    protected $fillable = [
        'matricula_id',
        'competencia_id',
        'periodo_id',
        'docente_id',
        'nota',
        'tipo_calificacion',
        'escala_id',
        'tipo_evaluacion',
        'fecha_registro',
        'observacion'
    ];
    
    protected $casts = [
        'fecha_registro' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Valores permitidos según tipo de calificación
    const TIPOS_CALIFICACION = [
        'NUMERICA' => 'Numérica (0-20)',
        'LITERAL' => 'Literal (AD, A, B, C)',
        'CUALITATIVA' => 'Cualitativa (Logro Destacado, En Proceso, etc.)'
    ];
    
    const TIPOS_EVALUACION = [
        'BIMESTRAL' => 'Evaluación Bimestral',
        'RECUPERACION' => 'Evaluación de Recuperación',
        'SUSTITUTORIO' => 'Evaluación Sustitutoria'
    ];
    
    // Relaciones
    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }
    
    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'competencia_id');
    }
    
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }
    
    // Relación con User (docente que registró la nota)
    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
    
    public function escala(): BelongsTo
    {
        return $this->belongsTo(EscalaCalificacion::class, 'escala_id');
    }
    
    // Validación automática al guardar
    public static function boot()
    {
        parent::boot();
        
        static::saving(function ($nota) {
            return $nota->validateNota();
        });
    }
    
    public function validateNota(): bool
    {
        if ($this->tipo_calificacion === 'NUMERICA') {
            $valor = floatval($this->nota);
            if ($valor < 0 || $valor > 20) {
                throw ValidationException::withMessages([
                    'nota' => 'La nota numérica debe estar entre 0 y 20'
                ]);
            }
            // Formatear número
            $this->nota = number_format($valor, 2, '.', '');
        }
        
        if ($this->tipo_calificacion === 'LITERAL') {
            $literales_permitidos = ['AD', 'A', 'B', 'C', 'CND','EXO', 'PENDIENTE'];
            if (!in_array(strtoupper($this->nota), $literales_permitidos)) {
                throw ValidationException::withMessages([
                    'nota' => 'La calificación literal no es válida. Permitidas: ' . implode(', ', $literales_permitidos)
                ]);
            }
            $this->nota = strtoupper($this->nota);
        }
        
        return true;
    }
    
    // Accesor para mostrar nota formateada
    public function getNotaFormateadaAttribute(): string
    {
        if ($this->tipo_calificacion === 'NUMERICA') {
            return floatval($this->nota) . ' pts';
        }
        
        if ($this->tipo_calificacion === 'LITERAL' && $this->escala) {
            return "{$this->nota} - {$this->escala->nombre}";
        }
        
        return $this->nota;
    }
    
    // Scope para filtrar por rango de notas numéricas
    public function scopeRangoNumerico($query, $min, $max)
    {
        return $query->where('tipo_calificacion', 'NUMERICA')
                     ->whereRaw('CAST(nota AS DECIMAL(5,2)) BETWEEN ? AND ?', [$min, $max]);
    }
    
    // Scope para filtrar por calificación literal
    public function scopeCalificacionLiteral($query, $calificacion)
    {
        return $query->where('tipo_calificacion', 'LITERAL')
                     ->where('nota', strtoupper($calificacion));
    }

    public function conclusionDescriptiva()
    {
        return $this->hasOne(ConclusionDescriptiva::class, 'nota_id');
    }

    // Accesor para saber si tiene conclusión
    public function getTieneConclusionAttribute(): bool
    {
        return $this->conclusionDescriptiva !== null;
    }
}