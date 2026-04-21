<?php
// app/Models/RegistroEvaluacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroEvaluacion extends Model
{
    protected $table = 'registro_evaluaciones';
    
    protected $fillable = [
        'matricula_id',
        'evaluacion_id',
        'periodo_id',
        'docente_id',
        'valoracion',
        'comentario',
        'fecha_registro'
    ];
    
    protected $casts = [
        'fecha_registro' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    const VALORACIONES = [
        'SIEMPRE' => 'Siempre',
        'CASI SIEMPRE' => 'Casi Siempre',
        'ALGUNAS VECES' => 'Algunas Veces',
        'NUNCA' => 'Nunca'
    ];
    
    
    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
    
    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Evaluacion::class, 'evaluacion_id');
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
    
    public function getValoracionNombreAttribute()
    {
        return self::VALORACIONES[$this->valoracion] ?? $this->valoracion;
    }
}