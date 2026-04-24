<?php
// app/Models/TipoNota.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoNota extends Model
{
    use SoftDeletes;
    
    protected $table = 'tipos_notas';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo_dato',
        'valor_numerico',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'valor_numerico' => 'decimal:2',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];
    
    public function modulos(): BelongsToMany
    {
        return $this->belongsToMany(ModuloRegistro::class, 'modulo_tipos_notas', 'tipo_nota_id', 'modulo_id')
                    ->withPivot('activo')
                    ->withTimestamps();
    }
    
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeLiteral($query)
    {
        return $query->where('tipo_dato', 'LITERAL');
    }
    
    public function scopeNumerico($query)
    {
        return $query->where('tipo_dato', 'NUMERICO');
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('codigo');
    }
}