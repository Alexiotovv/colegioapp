<?php
// app/Models/Docente.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Docente extends Model
{
    use SoftDeletes;
    
    protected $table = 'docentes';
    
    protected $fillable = [
        'dni',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'especialidad',
        'telefono',
        'email',
        'fecha_ingreso',
        'estado'  // ← Usa 'estado', no 'activo'
    ];
    
    protected $casts = [
        'fecha_ingreso' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Constantes
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';
    const ESTADO_VACACIONES = 'vacaciones';
    
    const ESTADOS = [
        self::ESTADO_ACTIVO => 'Activo',
        self::ESTADO_INACTIVO => 'Inactivo',
        self::ESTADO_VACACIONES => 'Vacaciones',
    ];
    
    // Accessors
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellido_paterno} {$this->apellido_materno}, {$this->nombres}";
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }
    
    // Helper methods
    public function isActivo(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }
}