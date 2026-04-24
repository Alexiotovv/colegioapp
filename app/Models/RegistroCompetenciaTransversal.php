<?php
// app/Models/RegistroCompetenciaTransversal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroCompetenciaTransversal extends Model
{
    protected $table = 'registro_competencias_transversales';
    
    protected $fillable = [
        'matricula_id',
        'competencia_transversal_id',
        'periodo_id',
        'docente_id',
        'nota',
        'tipo_calificacion',
        'conclusion',
        'fecha_registro'
    ];
    
    protected $casts = [
        'fecha_registro' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    const TIPOS_CALIFICACION = [
        'NUMERICA' => 'Numérica (0-20)',
        'LITERAL' => 'Literal (AD, A, B, C, CND)'
    ];
    
    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
    
    public function competenciaTransversal(): BelongsTo
    {
        return $this->belongsTo(CompetenciaTransversal::class, 'competencia_transversal_id');
    }
    
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }
    
    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
    
    public function getAlumnoAttribute()
    {
        return $this->matricula ? $this->matricula->alumno : null;
    }
}