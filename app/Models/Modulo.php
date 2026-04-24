<?php
// app/Models/Modulo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modulo extends Model
{
    protected $table = 'modulos';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'ruta',
        'icono',
        'padre_id',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];
    
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'rol_modulo', 'modulo_id', 'rol_id')
                    ->withPivot('activo')
                    ->withTimestamps();
    }
    
    public function usuariosExtra(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_modulo_extra', 'modulo_id', 'usuario_id')
                    ->withPivot('activo')
                    ->withTimestamps();
    }
    
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }
}