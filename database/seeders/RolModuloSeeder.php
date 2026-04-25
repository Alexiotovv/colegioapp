<?php
// database/seeders/RolModuloSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Modulo;

class RolModuloSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener módulos por código
        $modulos = Modulo::all()->keyBy('codigo');
        
        // ADMIN - todos los módulos
        $admin = Role::where('nombre', 'admin')->first();
        if ($admin) {
            $admin->modulos()->sync($modulos->pluck('id')->toArray());
        }
        
        // DOCENTE
        $docente = Role::where('nombre', 'docente')->first();
        if ($docente) {
            $docenteModulos = [
                'dashboard', 'carga-horaria', 'notas',
                'apreciaciones', 'registro-evaluaciones',
                'registro-otras-evaluaciones', 'registro-asistencias',
                'registro-competencias-transversales'
            ];
            $ids = $modulos->whereIn('codigo', $docenteModulos)->pluck('id')->toArray();
            $docente->modulos()->sync($ids);
        }
        
        // DIRECTOR
        $director = Role::where('nombre', 'director')->first();
        if ($director) {
            $directorModulos = [
                'dashboard', 'anios', 'periodos', 'alumnos', 'apoderados',
                'matriculas', 'carga-horaria', 'notas', 'libretas'
            ];
            $ids = $modulos->whereIn('codigo', $directorModulos)->pluck('id')->toArray();
            $director->modulos()->sync($ids);
        }
        
        // APODERADO
        $apoderado = Role::where('nombre', 'apoderado')->first();
        if ($apoderado) {
            $apoderadoModulos = ['dashboard', 'notas', 'registro-evaluaciones', 'registro-asistencias'];
            $ids = $modulos->whereIn('codigo', $apoderadoModulos)->pluck('id')->toArray();
            $apoderado->modulos()->sync($ids);
        }
        
        $this->command->info('✓ Módulos asignados a roles');
    }
}