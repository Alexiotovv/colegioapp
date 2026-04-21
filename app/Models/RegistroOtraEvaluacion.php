<?php
// app/Models/RegistroOtraEvaluacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroOtraEvaluacion extends Model
{
    protected $table = 'registro_otras_evaluaciones';
    
    protected $fillable = [
        'matricula_id',
        'tipo_otra_evaluacion_id',
        'periodo_id',
        'docente_id',
        'valor',
        'observacion',
        'fecha_registro'
    ];
    
    protected $casts = [
        'fecha_registro' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
    
    public function tipoOtraEvaluacion(): BelongsTo
    {
        return $this->belongsTo(TipoOtraEvaluacion::class, 'tipo_otra_evaluacion_id');
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