<?php
// app/Models/TipoOtraEvaluacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoOtraEvaluacion extends Model
{
    use SoftDeletes;
    
    protected $table = 'tipos_otras_evaluaciones';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo_dato',
        'min_valor',
        'max_valor',
        'opciones_literales',
        'nivel_id',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'min_valor' => 'integer',
        'max_valor' => 'integer',
        'opciones_literales' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    const TIPO_NUMERICO = 'NUMERICO';
    const TIPO_LITERAL = 'LITERAL';
    
    const TIPOS_DATO = [
        self::TIPO_NUMERICO => 'Numérico (1-40)',
        self::TIPO_LITERAL => 'Literal (AD, A, B, C, ND)',
    ];
    
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }
    
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }
    
    public function getOpcionesListaAttribute()
    {
        if ($this->tipo_dato === self::TIPO_LITERAL && $this->opciones_literales) {
            return implode(', ', $this->opciones_literales);
        }
        return '';
    }
}