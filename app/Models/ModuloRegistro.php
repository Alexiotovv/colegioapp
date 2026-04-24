<?php
// app/Models/ModuloRegistro.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModuloRegistro extends Model
{
    protected $table = 'modulos_registro';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'ruta',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
    ];
    
    public function tiposNotas(): BelongsToMany
    {
        return $this->belongsToMany(TipoNota::class, 'modulo_tipos_notas', 'modulo_id', 'tipo_nota_id')
                    ->withPivot('activo')
                    ->withTimestamps();
    }
    
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function getTiposNotasOptions()
    {
        return $this->tiposNotas()
            ->wherePivot('activo', true)
            ->orderBy('orden')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'codigo' => $item->codigo,
                    'nombre' => $item->nombre,
                    'tipo_dato' => $item->tipo_dato,
                    'valor_numerico' => $item->valor_numerico
                ];
            });
    }
}