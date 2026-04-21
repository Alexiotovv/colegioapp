<?php
// app/Models/Apreciacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apreciacion extends Model
{
    protected $table = 'apreciaciones';
    
    protected $fillable = [
        'matricula_id',
        'periodo_id',
        'docente_id',
        'apreciacion',
        'fecha_registro'
    ];
    
    protected $casts = [
        'fecha_registro' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relaciones
    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
    
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }
    
    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
    
    // Accesor para obtener el alumno
    public function getAlumnoAttribute()
    {
        return $this->matricula ? $this->matricula->alumno : null;
    }
}