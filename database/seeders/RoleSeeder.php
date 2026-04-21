<?php
// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tabla antes de insertar (opcional)
        Schema::disableForeignKeyConstraints();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        $roles = [
            [
                'nombre' => 'admin',
                'descripcion' => 'Administrador del sistema - Acceso total',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'director',
                'descripcion' => 'Director del colegio - Gestión académica y administrativa',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'docente',
                'descripcion' => 'Docente - Registro de notas y gestión de cursos',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'apoderado',
                'descripcion' => 'Apoderado/Padre de familia - Seguimiento de notas y pagos',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
        
        $this->command->info('✓ Roles creados exitosamente');
    }
}