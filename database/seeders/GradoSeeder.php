<?php
// database/seeders/GradoSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('grados')->truncate();
        Schema::enableForeignKeyConstraints();

        // Obtener IDs de niveles
        $primariaId = DB::table('niveles')->where('nombre', 'Primaria')->value('id');
        $secundariaId = DB::table('niveles')->where('nombre', 'Secundaria')->value('id');

        $grados = [
            // Primaria
            ['nivel_id' => $primariaId, 'nombre' => '1ro', 'orden' => 1, 'activo' => true],
            ['nivel_id' => $primariaId, 'nombre' => '2do', 'orden' => 2, 'activo' => true],
            ['nivel_id' => $primariaId, 'nombre' => '3ro', 'orden' => 3, 'activo' => true],
            ['nivel_id' => $primariaId, 'nombre' => '4to', 'orden' => 4, 'activo' => true],
            ['nivel_id' => $primariaId, 'nombre' => '5to', 'orden' => 5, 'activo' => true],
            ['nivel_id' => $primariaId, 'nombre' => '6to', 'orden' => 6, 'activo' => true],
            
            // Secundaria
            ['nivel_id' => $secundariaId, 'nombre' => '1ro', 'orden' => 1, 'activo' => true],
            ['nivel_id' => $secundariaId, 'nombre' => '2do', 'orden' => 2, 'activo' => true],
            ['nivel_id' => $secundariaId, 'nombre' => '3ro', 'orden' => 3, 'activo' => true],
            ['nivel_id' => $secundariaId, 'nombre' => '4to', 'orden' => 4, 'activo' => true],
            ['nivel_id' => $secundariaId, 'nombre' => '5to', 'orden' => 5, 'activo' => true],
        ];

        foreach ($grados as $grado) {
            DB::table('grados')->insert(array_merge($grado, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        $this->command->info('✓ Grados creados');
    }
}