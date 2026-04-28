<?php
// app/Models/EvaluacionActitudinal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluacionActitudinal extends Model
{
    use SoftDeletes;
    
    protected $table = 'eval_actitudinales'; // ← Nombre corto
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel_id',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];
    
    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }
    
    public function registros()
    {
        return $this->hasMany(RegistroEvaluacionActitudinal::class, 'eval_actitudinal_id');
    }
}