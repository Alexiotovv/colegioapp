<?php
// app/Models/DescripcionCuadroDinamico.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescripcionCuadroDinamico extends Model
{
    protected $table = 'descripcion_cuadros_dinamicos';

    protected $fillable = ['cuadro_id', 'texto', 'dato', 'orden'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function cuadro()
    {
        return $this->belongsTo(CuadroDinamico::class, 'cuadro_id');
    }
}
