<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvanceRegistroNotaHabilitacion extends Model
{
    protected $table = 'avance_registro_nota_habilitaciones';

    protected $fillable = [
        'periodo_id',
        'habilitado',
    ];

    protected $casts = [
        'habilitado' => 'boolean',
    ];

    public static function estaHabilitado(?int $periodoId): bool
    {
        if (!$periodoId) {
            return false;
        }

        return (bool) static::query()
            ->where('periodo_id', $periodoId)
            ->value('habilitado');
    }
}