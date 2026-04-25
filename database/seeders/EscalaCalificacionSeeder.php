<?php
// database/seeders/EscalaCalificacionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EscalaCalificacionSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('escalas_calificacion')->truncate();
        Schema::enableForeignKeyConstraints();

        $escalas = [
            [
                'codigo' => 'AD',
                'nombre' => 'Logro Destacado',
                'descripcion' => 'Demuestra un nivel superior al esperado',
                'valor_numerico_min' => 18,
                'valor_numerico_max' => 20,
                'nivel' => 'AMBOS',
                'aprobatorio' => true,
                'orden' => 1,
                'activo' => true,
            ],
            [
                'codigo' => 'A',
                'nombre' => 'Logro Esperado',
                'descripcion' => 'Alcanza el nivel esperado',
                'valor_numerico_min' => 14,
                'valor_numerico_max' => 17,
                'nivel' => 'AMBOS',
                'aprobatorio' => true,
                'orden' => 2,
                'activo' => true,
            ],
            [
                'codigo' => 'B',
                'nombre' => 'En Proceso',
                'descripcion' => 'Está próximo al nivel esperado',
                'valor_numerico_min' => 11,
                'valor_numerico_max' => 13,
                'nivel' => 'AMBOS',
                'aprobatorio' => false,
                'orden' => 3,
                'activo' => true,
            ],
            [
                'codigo' => 'C',
                'nombre' => 'En Inicio',
                'descripcion' => 'Muestra dificultades y requiere apoyo',
                'valor_numerico_min' => 0,
                'valor_numerico_max' => 10,
                'nivel' => 'AMBOS',
                'aprobatorio' => false,
                'orden' => 4,
                'activo' => true,
            ],
            [
                'codigo' => 'CND',
                'nombre' => 'Competencia No Desarrollada',
                'descripcion' => 'No ha desarrollado la competencia',
                'valor_numerico_min' => null,
                'valor_numerico_max' => null,
                'nivel' => 'AMBOS',
                'aprobatorio' => false,
                'orden' => 5,
                'activo' => true,
            ],
                        [
                'codigo' => 'ND',
                'nombre' => 'No Desarrollada',
                'descripcion' => 'No se ha asignado para el desarrollo de la competencia',
                'valor_numerico_min' => null,
                'valor_numerico_max' => null,
                'nivel' => 'AMBOS',
                'aprobatorio' => false,
                'orden' => 5,
                'activo' => true,
            ],
        ];

        foreach ($escalas as $escala) {
            DB::table('escalas_calificacion')->insert(array_merge($escala, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        $this->command->info('✓ Escalas de calificación creadas');
    }
}