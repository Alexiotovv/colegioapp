<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';
    
    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
        'tipo',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Obtener valor por clave
    public static function getValor($clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();
        if (!$config) {
            return $default;
        }
        
        switch ($config->tipo) {
            case 'numero':
                return (int) $config->valor;
            case 'array':
                return json_decode($config->valor, true);
            case 'json':
                return json_decode($config->valor);
            default:
                return $config->valor;
        }
    }
    
    // Actualizar o crear configuración
    public static function setValor($clave, $valor, $descripcion = null, $tipo = 'texto')
    {
        return self::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => is_array($valor) ? json_encode($valor) : $valor,
                'descripcion' => $descripcion,
                'tipo' => $tipo
            ]
        );
    }
}