<?php
// app/Models/ConfiguracionLibreta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionLibreta extends Model
{
    protected $table = 'configuracion_libreta';
    
    protected $fillable = [
        'titulo',
        'subtitulo',
        'dre',
        'ugel',
        'logo_pais',
        'logo_region',
        'logo_institucion',
        'firma_director',
        'nombre_director',
        'cargo_director',
        'firma_tutor',
        'nombre_tutor',
        'cargo_tutor',
        'texto_pie',
        'mostrar_en_libreta',
        'nombre_subdirector',
        'firma_subdirector',
        'cargo_subdirector',  
    ];
    
    protected $casts = [
        'mostrar_en_libreta' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Obtener la configuración (si no existe, crear una por defecto)
    public static function getConfig()
    {
        $config = self::first();
        if (!$config) {
            $config = self::create([
                'titulo' => 'Libreta de Notas',
                'mostrar_en_libreta' => true,
            ]);
        }
        return $config;
    }
}