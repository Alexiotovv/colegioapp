<?php
// database/seeders/PeriodoSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PeriodoSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('periodos')->truncate();
        Schema::enableForeignKeyConstraints();

        $anioActivoId = DB::table('anio_academicos')->where('activo', true)->value('id');

        $periodos = [
            [
                'anio_academico_id' => $anioActivoId,
                'nombre' => 'I Bimestre',
                'orden' => 1,
                'fecha_inicio' => '2025-03-01',
                'fecha_fin' => '2025-04-30',
                'activo' => true,
            ],
            [
                'anio_academico_id' => $anioActivoId,
                'nombre' => 'II Bimestre',
                'orden' => 2,
                'fecha_inicio' => '2025-05-01',
                'fecha_fin' => '2025-06-30',
                'activo' => true,
            ],
            [
                'anio_academico_id' => $anioActivoId,
                'nombre' => 'III Bimestre',
                'orden' => 3,
                'fecha_inicio' => '2025-08-01',
                'fecha_fin' => '2025-09-30',
                'activo' => true,
            ],
            [
                'anio_academico_id' => $anioActivoId,
                'nombre' => 'IV Bimestre',
                'orden' => 4,
                'fecha_inicio' => '2025-10-01',
                'fecha_fin' => '2025-12-15',
                'activo' => true,
            ],
        ];

        foreach ($periodos as $periodo) {
            DB::table('periodos')->insert(array_merge($periodo, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        $this->command->info('✓ Periodos académicos creados');
    }
}