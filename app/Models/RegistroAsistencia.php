<?php
// app/Models/RegistroAsistencia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroAsistencia extends Model
{
    protected $table = 'registro_asistencias';
    
    protected $fillable = [
        'matricula_id',
        'tipo_inasistencia_id',
        'periodo_id',
        'docente_id',
        'cantidad',
        'observacion',
        'fecha_registro'
    ];
    
    protected $casts = [
        'cantidad' => 'integer',
        'fecha_registro' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
    
    public function tipoInasistencia(): BelongsTo
    {
        return $this->belongsTo(TipoInasistencia::class, 'tipo_inasistencia_id');
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