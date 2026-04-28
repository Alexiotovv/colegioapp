<?php
// app/Models/RegistroEvaluacionActitudinal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroEvaluacionActitudinal extends Model
{
    protected $table = 'reg_eval_actitudinales'; // ← Nombre corto
    
    protected $fillable = [
        'matricula_id',
        'eval_actitudinal_id',
        'periodo_id',
        'docente_id',
        'valoracion',
        'comentario',
        'fecha_registro'
    ];
    
    protected $casts = [
        'fecha_registro' => 'date',
    ];
    
    const VALORACIONES = [
        'SIEMPRE' => 'Siempre',
        'CASI SIEMPRE' => 'Casi Siempre',
        'ALGUNAS VECES' => 'Algunas Veces',
        'NUNCA' => 'Nunca'
    ];
    
    public function matricula()
    {
        return $this->belongsTo(Matricula::class);
    }
    
    public function evaluacionActitudinal()
    {
        return $this->belongsTo(EvaluacionActitudinal::class, 'eval_actitudinal_id');
    }
    
    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }
    
    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
    
    public function getValoracionNombreAttribute()
    {
        return self::VALORACIONES[$this->valoracion] ?? $this->valoracion;
    }
}