<?php
// app/Models/ConfiguracionAvanceCuadro.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionAvanceCuadro extends Model
{
    public const CONCLUSION_VISIBLE_KEY = '__mostrar_conclusion_descriptiva__';

    protected $table = 'configuracion_avance_cuadros';

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

    public static function isConclusionVisibleForNivel($nivelId): bool
    {
        $cuadros = self::getCuadrosForNivel($nivelId);

        // Por compatibilidad, si el nivel no tiene configuración guardada, se mantiene visible.
        if ($cuadros === null) {
            return true;
        }

        return in_array(self::CONCLUSION_VISIBLE_KEY, $cuadros, true);
    }
}
