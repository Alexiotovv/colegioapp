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
                'fecha_inicio' => '2026-03-01',
                'fecha_fin' => '2026-04-30',
                'activo' => false,
            ],
            [
                'anio_academico_id' => $anioActivoId,
                'nombre' => 'II Bimestre',
                'orden' => 2,
                'fecha_inicio' => '2026-05-01',
                'fecha_fin' => '2026-06-30',
                'activo' => false,
            ],
            [
                'anio_academico_id' => $anioActivoId,
                'nombre' => 'III Bimestre',
                'orden' => 3,
                'fecha_inicio' => '2026-08-01',
                'fecha_fin' => '2026-09-30',
                'activo' => false,
            ],
            [
                'anio_academico_id' => $anioActivoId,
                'nombre' => 'IV Bimestre',
                'orden' => 4,
                'fecha_inicio' => '2026-10-01',
                'fecha_fin' => '2026-12-15',
                'activo' => false,
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