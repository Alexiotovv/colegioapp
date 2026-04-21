<?php
// database/seeders/AnioAcademicoSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnioAcademicoSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('anio_academicos')->truncate();
        Schema::enableForeignKeyConstraints();

        $anios = [
            [
                'anio' => '2024',
                'fecha_inicio' => '2024-03-01',
                'fecha_fin' => '2024-12-20',
                'activo' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'anio' => '2025',
                'fecha_inicio' => '2025-03-01',
                'fecha_fin' => '2025-12-20',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('anio_academicos')->insert($anios);
        
        $this->command->info('✓ Años académicos creados');
    }
}