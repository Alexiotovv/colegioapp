<?php
// app/Models/ConfiguracionLibretaCuadro.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionLibretaCuadro extends Model
{
    protected $table = 'configuracion_libreta_cuadros';

    protected $fillable = [
        'nivel_id',
        'cuadros',
    ];

    protected $casts = [
        'cuadros' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // devolver array de cuadros habilitados para un nivel (si no existe, devolver null)
    public static function getCuadrosForNivel($nivelId)
    {
        $rec = self::where('nivel_id', $nivelId)->first();
        return $rec ? ($rec->cuadros ?? []) : null;
    }

    public static function setCuadrosForNivel($nivelId, array $cuadros)
    {
        return self::updateOrCreate(
            ['nivel_id' => $nivelId],
            ['cuadros' => $cuadros]
        );
    }
}
