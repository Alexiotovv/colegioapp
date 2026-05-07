<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar configuraciones de límite de caracteres si no existen
        $configurations = [
            [
                'clave' => 'conclusiones_caracteres_max',
                'valor' => '500',
                'descripcion' => 'Cantidad máxima de caracteres permitidos en las conclusiones descriptivas',
                'tipo' => 'numero',
            ],
            [
                'clave' => 'competencias_transversales_caracteres_max',
                'valor' => '500',
                'descripcion' => 'Cantidad máxima de caracteres permitidos en conclusiones de competencias transversales',
                'tipo' => 'numero',
            ],
            [
                'clave' => 'apreciaciones_caracteres_max',
                'valor' => '500',
                'descripcion' => 'Cantidad máxima de caracteres permitidos en las apreciaciones del tutor',
                'tipo' => 'numero',
            ],
        ];

        foreach ($configurations as $config) {
            DB::table('configuraciones')->updateOrInsert(
                ['clave' => $config['clave']],
                array_merge($config, [
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('configuraciones')->whereIn('clave', [
            'conclusiones_caracteres_max',
            'competencias_transversales_caracteres_max',
            'apreciaciones_caracteres_max',
        ])->delete();
    }
};
