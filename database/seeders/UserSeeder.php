<?php
// database/seeders/UserSeeder.php - Versión CORREGIDA

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        $users = [
            // Administrador
            [
                'name' => 'Administrador del Sistema',
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'role_id' => 1,
                'activo' => true,
                'userable_type' => null,  // 🔥 AGREGADO
                'userable_id' => null,     // 🔥 AGREGADO
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Director
            [
                'name' => 'María Elena Campos',
                'username' => 'directora',
                'email' => 'directora@gmail.com',
                'password' => Hash::make('director123'),
                'role_id' => 2,
                'activo' => true,
                'userable_type' => null,
                'userable_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Docente de Matemáticas
            [
                'name' => 'Carlos Alberto Mendoza',
                'username' => 'carlos.mendoza',
                'email' => 'carlos.mendoza@gmail.com',
                'password' => Hash::make('docente123'),
                'role_id' => 3,
                'activo' => true,
                'userable_type' => null,
                'userable_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Auxiliar
            [
                'name' => 'María López Huamán',
                'username' => 'maria.lopez',
                'email' => 'maria.lopez@gmail.com',
                'password' => Hash::make('auxiliar123'),
                'role_id' => 4,
                'activo' => true,
                'userable_type' => null,
                'userable_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
        
        $this->command->info('✓ Usuarios creados exitosamente');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📝 Credenciales de acceso:');
        $this->command->info('   Admin:      admin / admin123');
        $this->command->info('   Director:   directora / director123');
        $this->command->info('   Docente:    carlos.mendoza / docente123');
        $this->command->info('   Auxiliar:  maria.lopez / auxiliar123');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}