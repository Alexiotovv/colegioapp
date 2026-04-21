<?php
// app/Models/ConfiguracionInstitucion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionInstitucion extends Model
{
    protected $table = 'configuracion_institucion';
    
    protected $fillable = [
        'nombre',
        'ruc',
        'direccion',
        'telefono',
        'telefono2',
        'email',
        'logo_login',
        'logo_dashboard',
        'favicon',
        'descripcion',
        'web'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Obtener la configuración (si no existe, crear una por defecto)
    public static function getConfig()
    {
        $config = self::first();
        if (!$config) {
            $config = self::create([
                'nombre' => 'Mi Colegio',
                'ruc' => '20123456789',
                'direccion' => 'Av. Principal 123',
                'telefono' => '987654321',
                'email' => 'info@micolegio.edu.pe',
            ]);
        }
        return $config;
    }
}