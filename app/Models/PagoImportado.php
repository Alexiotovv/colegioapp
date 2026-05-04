<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoImportado extends Model
{
    protected $table = 'pagos_importados';

    protected $fillable = [
        'anio_emision',
        'numero_fila',
        'estudiante',
        'dni_est',
        'doc_facturacion_dni',
        'nombre_facturacion',
        'nivel',
        'grado',
        'seccion',
        'marzo',
        'abril',
        'mayo',
        'junio',
        'julio',
        'agosto',
        'setiembre',
        'octubre',
        'noviembre',
        'diciembre',
        'total',
    ];

    protected $casts = [
        'anio_emision' => 'integer',
        'numero_fila' => 'integer',
        'marzo' => 'decimal:2',
        'abril' => 'decimal:2',
        'mayo' => 'decimal:2',
        'junio' => 'decimal:2',
        'julio' => 'decimal:2',
        'agosto' => 'decimal:2',
        'setiembre' => 'decimal:2',
        'octubre' => 'decimal:2',
        'noviembre' => 'decimal:2',
        'diciembre' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('estudiante', 'like', "%{$search}%")
                ->orWhere('dni_est', 'like', "%{$search}%")
                ->orWhere('doc_facturacion_dni', 'like', "%{$search}%")
                ->orWhere('nombre_facturacion', 'like', "%{$search}%");
        });
    }
}
