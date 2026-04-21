<?php
// app/Models/AnioAcademico.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes; // 🔥 COMENTAR O ELIMINAR ESTA LÍNEA

class AnioAcademico extends Model
{
    // use SoftDeletes; // 🔥 COMENTAR O ELIMINAR ESTA LÍNEA
    
    protected $table = 'anio_academicos';
    
    protected $fillable = [
        'anio',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];
    
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];
    
    // Relaciones
    public function matriculas()
    {
        return $this->hasMany(Matricula::class);
    }
    
    public function periodos()
    {
        return $this->hasMany(Periodo::class);
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}