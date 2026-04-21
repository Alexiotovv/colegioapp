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
            
            // Docentes de Comunicación
            [
                'dni' => '12345680',
                'nombres' => 'Juan Carlos',
                'apellido_paterno' => 'Ramírez',
                'apellido_materno' => 'Torres',
                'especialidad' => 'Comunicación',
                'telefono' => '987654323',
                'email' => 'juan.ramirez@colcoopcv.edu.pe',
                'fecha_ingreso' => '2019-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '12345681',
                'nombres' => 'María Elena',
                'apellido_paterno' => 'Gonzales',
                'apellido_materno' => 'Rojas',
                'especialidad' => 'Comunicación',
                'telefono' => '987654324',
                'email' => 'maria.gonzales@colcoopcv.edu.pe',
                'fecha_ingreso' => '2020-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Docentes de Ciencia y Tecnología
            [
                'dni' => '12345682',
                'nombres' => 'Luis Alberto',
                'apellido_paterno' => 'Castillo',
                'apellido_materno' => 'Sánchez',
                'especialidad' => 'Ciencia y Tecnología',
                'telefono' => '987654325',
                'email' => 'luis.castillo@colcoopcv.edu.pe',
                'fecha_ingreso' => '2018-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '12345683',
                'nombres' => 'Ana María',
                'apellido_paterno' => 'Huamán',
                'apellido_materno' => 'Vargas',
                'especialidad' => 'Ciencia y Tecnología',
                'telefono' => '987654326',
                'email' => 'ana.huaman@colcoopcv.edu.pe',
                'fecha_ingreso' => '2021-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Docentes de Inglés
            [
                'dni' => '12345684',
                'nombres' => 'Patricia',
                'apellido_paterno' => 'López',
                'apellido_materno' => 'Mendoza',
                'especialidad' => 'Inglés',
                'telefono' => '987654327',
                'email' => 'patricia.lopez@colcoopcv.edu.pe',
                'fecha_ingreso' => '2020-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '12345685',
                'nombres' => 'Robert',
                'apellido_paterno' => 'Smith',
                'apellido_materno' => 'Johnson',
                'especialidad' => 'Inglés',
                'telefono' => '987654328',
                'email' => 'robert.smith@colcoopcv.edu.pe',
                'fecha_ingreso' => '2019-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Docentes de Educación Física
            [
                'dni' => '12345686',
                'nombres' => 'Miguel Ángel',
                'apellido_paterno' => 'Díaz',
                'apellido_materno' => 'Cáceres',
                'especialidad' => 'Educación Física',
                'telefono' => '987654329',
                'email' => 'miguel.diaz@colcoopcv.edu.pe',
                'fecha_ingreso' => '2018-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '12345687',
                'nombres' => 'Lucía',
                'apellido_paterno' => 'Torres',
                'apellido_materno' => 'Peralta',
                'especialidad' => 'Educación Física',
                'telefono' => '987654330',
                'email' => 'lucia.torres@colcoopcv.edu.pe',
                'fecha_ingreso' => '2021-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Docentes de Religión
            [
                'dni' => '12345688',
                'nombres' => 'José',
                'apellido_paterno' => 'Cruz',
                'apellido_materno' => 'Mamani',
                'especialidad' => 'Religión',
                'telefono' => '987654331',
                'email' => 'jose.cruz@colcoopcv.edu.pe',
                'fecha_ingreso' => '2019-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '12345689',
                'nombres' => 'Teresa',
                'apellido_paterno' => 'Flores',
                'apellido_materno' => 'Rivera',
                'especialidad' => 'Religión',
                'telefono' => '987654332',
                'email' => 'teresa.flores@colcoopcv.edu.pe',
                'fecha_ingreso' => '2020-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Docentes de Arte y Cultura
            [
                'dni' => '12345690',
                'nombres' => 'Fernando',
                'apellido_paterno' => 'Ríos',
                'apellido_materno' => 'Chávez',
                'especialidad' => 'Arte y Cultura',
                'telefono' => '987654333',
                'email' => 'fernando.rios@colcoopcv.edu.pe',
                'fecha_ingreso' => '2020-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '12345691',
                'nombres' => 'Carmen Rosa',
                'apellido_paterno' => 'Vega',
                'apellido_materno' => 'Soto',
                'especialidad' => 'Arte y Cultura',
                'telefono' => '987654334',
                'email' => 'carmen.vega@colcoopcv.edu.pe',
                'fecha_ingreso' => '2021-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Docentes de Ciencias Sociales
            [
                'dni' => '12345692',
                'nombres' => 'Jorge Luis',
                'apellido_paterno' => 'Mamani',
                'apellido_materno' => 'Quispe',
                'especialidad' => 'Ciencias Sociales',
                'telefono' => '987654335',
                'email' => 'jorge.mamani@colcoopcv.edu.pe',
                'fecha_ingreso' => '2018-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '12345693',
                'nombres' => 'Ruth',
                'apellido_paterno' => 'Sánchez',
                'apellido_materno' => 'Ramos',
                'especialidad' => 'Ciencias Sociales',
                'telefono' => '987654336',
                'email' => 'ruth.sanchez@colcoopcv.edu.pe',
                'fecha_ingreso' => '2019-03-01',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Docente inactivo (ejemplo)
            [
                'dni' => '12345694',
                'nombres' => 'Pedro',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'Pérez',
                'especialidad' => 'Matemáticas',
                'telefono' => '987654337',
                'email' => 'pedro.garcia@colcoopcv.edu.pe',
                'fecha_ingreso' => '2017-03-01',
                'estado' => 'inactivo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Docente en vacaciones (ejemplo)
            [
                'dni' => '12345695',
                'nombres' => 'Laura',
                'apellido_paterno' => 'Fernández',
                'apellido_materno' => 'Luna',
                'especialidad' => 'Comunicación',
                'telefono' => '987654338',
                'email' => 'laura.fernandez@colcoopcv.edu.pe',
                'fecha_ingreso' => '2018-03-01',
                'estado' => 'vacaciones',
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