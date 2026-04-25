<?php
// database/seeders/SeccionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeccionSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('secciones')->truncate();
        Schema::enableForeignKeyConstraints();

        $secciones = [
            ['nombre' => 'A', 'turno' => 'MAÑANA', 'activo' => true],
            ['nombre' => 'B', 'turno' => 'MAÑANA', 'activo' => true],
            ['nombre' => 'C', 'turno' => 'MAÑANA', 'activo' => true],
            ['nombre' => 'D', 'turno' => 'MAÑANA', 'activo' => true],
            ['nombre' => 'E', 'turno' => 'MAÑANA', 'activo' => true],
            
        ];

        foreach ($secciones as $seccion) {
            DB::table('secciones')->insert(array_merge($seccion, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        $this->command->info('✓ Secciones creadas');
    }
}