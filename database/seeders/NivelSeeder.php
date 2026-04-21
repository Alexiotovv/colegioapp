<?php
// database/seeders/NivelSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NivelSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('niveles')->truncate();
        Schema::enableForeignKeyConstraints();

        $niveles = [
            [
                'nombre' => 'Inicial',
                'descripcion' => 'Educación Inicial - 3 a 5 años',
                'orden' => 1,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Primaria',
                'descripcion' => 'Educación Primaria - 1ro a 6to grado',
                'orden' => 2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Secundaria',
                'descripcion' => 'Educación Secundaria - 1ro a 5to año',
                'orden' => 3,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('niveles')->insert($niveles);
        
        $this->command->info('✓ Niveles educativos creados');
    }
}