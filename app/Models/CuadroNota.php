<?php
// app/Models/CuadroNota.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuadroNota extends Model
{
    use SoftDeletes;
    
    protected $table = 'cuadros_notas';
    
    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'tipo',
        'configuracion',
        'activo',
        'orden'
    ];
    
    protected $casts = [
        'configuracion' => 'array',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];
    
    // Secciones disponibles del sistema
    public static function getSeccionesDisponibles()
    {
        return [
            ['id' => 'notas', 'nombre' => 'Notas por Competencia', 'icono' => 'fa-edit', 'descripcion' => 'Registro de notas por competencia y periodo'],
            ['id' => 'competencias_transversales', 'nombre' => 'Competencias Transversales', 'icono' => 'fa-exchange-alt', 'descripcion' => 'Competencias transversales por periodo'],
            ['id' => 'apreciaciones', 'nombre' => 'Apreciaciones del Tutor', 'icono' => 'fa-comment-dots', 'descripcion' => 'Apreciaciones del tutor por periodo'],
            ['id' => 'evaluaciones_padre', 'nombre' => 'Evaluación del Padre', 'icono' => 'fa-clipboard-list', 'descripcion' => 'Evaluación del padre de familia'],
            ['id' => 'inasistencias', 'nombre' => 'Inasistencias', 'icono' => 'fa-calendar-times', 'descripcion' => 'Registro de inasistencias por tipo'],
            ['id' => 'otras_evaluaciones', 'nombre' => 'Otras Evaluaciones', 'icono' => 'fa-tasks', 'descripcion' => 'Otras evaluaciones complementarias'],
            ['id' => 'promedios', 'nombre' => 'Promedios Finales', 'icono' => 'fa-chart-line', 'descripcion' => 'Promedios finales por área'],
        ];
    }
    
    // Obtener configuración de secciones activas
    public function getSeccionesActivas()
    {
        $config = $this->configuracion;
        $secciones = $config['secciones'] ?? [];
        
        // Filtrar solo las activas y ordenar
        $activas = array_filter($secciones, function($seccion) {
            return isset($seccion['activo']) && $seccion['activo'] === true;
        });
        
        usort($activas, function($a, $b) {
            return ($a['orden'] ?? 0) <=> ($b['orden'] ?? 0);
        });
        
        return $activas;
    }
    
    // Verificar si una sección está activa
    public function tieneSeccion($codigo)
    {
        $secciones = $this->configuracion['secciones'] ?? [];
        foreach ($secciones as $seccion) {
            if ($seccion['id'] === $codigo && ($seccion['activo'] ?? false)) {
                return true;
            }
        }
        return false;
    }
    
    // Duplicar un cuadro
    public function duplicate()
    {
        $newCuadro = $this->replicate();
        $newCuadro->nombre = $this->nombre . ' (Copia)';
        $newCuadro->codigo = $this->codigo . '-copia-' . time();
        $newCuadro->save();
        
        return $newCuadro;
    }
}