<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('🚀 Iniciando Seeders - Sistema Colegio');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Ejecutar seeders en orden correcto
        $this->call([
            RoleSeeder::class,              // 1. Roles primero
            UserSeeder::class,              // 2. Usuarios después
            NivelSeeder::class,             // 3. Niveles educativos
            GradoSeeder::class,             // 4. Grados
            SeccionSeeder::class,           // 5. Secciones
            AnioAcademicoSeeder::class,     // 6. Años académicos
            PeriodoSeeder::class,           // 7. Periodos/Bimestres
            EscalaCalificacionSeeder::class, // 8. Escalas de calificación
        ]);

        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✅ Todos los seeders ejecutados correctamente');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}