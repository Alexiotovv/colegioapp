<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoOrdenMerito extends Model
{
    use SoftDeletes;

    protected $table = 'tipos_orden_merito';

    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel_id',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
}
