<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroOrdenMerito extends Model
{
    protected $table = 'registro_orden_meritos';

    protected $fillable = [
        'matricula_id',
        'tipo_orden_merito_id',
        'nota_valor',
        'periodo_id',
        'docente_id',
        'observacion',
        'fecha_registro',
    ];

    protected $casts = [
        'nota_valor' => 'integer',
        'fecha_registro' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function tipoOrdenMerito(): BelongsTo
    {
        return $this->belongsTo(TipoOrdenMerito::class, 'tipo_orden_merito_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
}
