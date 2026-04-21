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
                'email' => 'admin@colcoopcv.edu.pe',
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
                'email' => 'directora@colcoopcv.edu.pe',
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
                'email' => 'carlos.mendoza@colcoopcv.edu.pe',
                'password' => Hash::make('docente123'),
                'role_id' => 3,
                'activo' => true,
                'userable_type' => null,
                'userable_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Docente de Comunicación
            [
                'name' => 'Rosa María Quispe',
                'username' => 'rosa.quispe',
                'email' => 'rosa.quispe@colcoopcv.edu.pe',
                'password' => Hash::make('docente123'),
                'role_id' => 3,
                'activo' => true,
                'userable_type' => null,
                'userable_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Apoderado 1
            [
                'name' => 'Juan Pérez Gonzales',
                'username' => 'juan.perez',
                'email' => 'juan.perez@email.com',
                'password' => Hash::make('apoderado123'),
                'role_id' => 4,
                'activo' => true,
                'userable_type' => null,
                'userable_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Apoderado 2
            [
                'name' => 'María López Huamán',
                'username' => 'maria.lopez',
                'email' => 'maria.lopez@email.com',
                'password' => Hash::make('apoderado123'),
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
        $this->command->info('   Apoderado:  juan.perez / apoderado123');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}