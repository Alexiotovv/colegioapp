<?php
// app/Models/PlantillaEvaluacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantillaEvaluacion extends Model
{
    protected $table = 'plantillas_evaluacion';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel_id',
        'evaluaciones_ids',
        'activo',
    ];
    
    protected $casts = [
        'evaluaciones_ids' => 'json',
        'activo' => 'boolean',
    ];
    
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }
    
    /**
     * Obtiene las evaluaciones asociadas a esta plantilla
     */
    public function getEvaluaciones()
    {
        return Evaluacion::whereIn('id', $this->evaluaciones_ids ?? [])->orderBy('orden')->get();
    }
    
    /**
     * Agrega una evaluación a la plantilla
     */
    public function agregarEvaluacion($evaluacionId): void
    {
        $ids = $this->evaluaciones_ids ?? [];
        if (!in_array($evaluacionId, $ids)) {
            $ids[] = $evaluacionId;
            $this->update(['evaluaciones_ids' => $ids]);
        }
    }
    
    /**
     * Remueve una evaluación de la plantilla
     */
    public function removerEvaluacion($evaluacionId): void
    {
        $ids = $this->evaluaciones_ids ?? [];
        $this->update(['evaluaciones_ids' => array_diff($ids, [$evaluacionId])]);
    }
}
