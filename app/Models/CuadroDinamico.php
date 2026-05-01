<?php
// app/Models/CuadroDinamico.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuadroDinamico extends Model
{
    protected $table = 'cuadros_dinamicos';

    protected $fillable = [
        'nombre', 'slug', 'nivel_id', 'tipo', 'nota_tipo', 'involucra_libreta', 'ancho', 'mostrar_en_libreta', 'orden', 'activo', 'opciones'
    ];

    protected $casts = [
        'involucra_libreta' => 'boolean',
        'mostrar_en_libreta' => 'boolean',
        'activo' => 'boolean',
        'opciones' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function descripciones()
    {
        return $this->hasMany(DescripcionCuadroDinamico::class, 'cuadro_id')->orderBy('orden');
    }
}
