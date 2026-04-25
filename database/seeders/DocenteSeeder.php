<?php
// database/seeders/DocenteSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tabla antes de insertar (opcional)
        Schema::disableForeignKeyConstraints();
        DB::table('docentes')->truncate();
        Schema::enableForeignKeyConstraints();

        $docentes = [
            // Docentes de Matemáticas
            [
                'dni' => '12345678',
                'nombres' => 'Carlos Alberto',
                'apellido_paterno' => 'Mendoza',
                'apellido_materno' => 'Paredes',
                'especialidad' => 'Matemáticas',
                'telefono' => '987654321',
                'email' => 'carlos.mendoza@colcoopcv.edu.pe',
                'fecha_ingreso' => '2020-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '12345679',
                'nombres' => 'Rosa María',
                'apellido_paterno' => 'Quispe',
                'apellido_materno' => 'Flores',
                'especialidad' => 'Matemáticas',
                'telefono' => '987654322',
                'email' => 'rosa.quispe@colcoopcv.edu.pe',
                'fecha_ingreso' => '2021-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            
        ];

        DB::table('docentes')->insert($docentes);
        
        $this->command->info('✓ Docentes creados exitosamente');
        $this->command->info('  Total: ' . count($docentes) . ' docentes registrados');
        $this->command->info('  - Activos: ' . count(array_filter($docentes, fn($d) => $d['estado'] === 'activo')));
        $this->command->info('  - Inactivos: ' . count(array_filter($docentes, fn($d) => $d['estado'] === 'inactivo')));
        $this->command->info('  - Vacaciones: ' . count(array_filter($docentes, fn($d) => $d['estado'] === 'vacaciones')));
    }
}