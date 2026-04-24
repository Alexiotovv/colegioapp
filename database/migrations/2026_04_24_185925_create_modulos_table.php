<?php
// database/migrations/2026_04_24_000010_create_modulos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 100);
            $table->string('ruta', 100)->nullable();
            $table->string('icono', 50)->nullable();
            $table->string('padre_id')->nullable(); // para submenús
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar módulos por defecto
        DB::table('modulos')->insert([
            // Módulos principales
            ['codigo' => 'dashboard', 'nombre' => 'Dashboard', 'ruta' => 'dashboard', 'icono' => 'fa-home', 'orden' => 1],
            
            // ADMINISTRACIÓN
            ['codigo' => 'users', 'nombre' => 'Usuarios', 'ruta' => 'admin.users.index', 'icono' => 'fa-users', 'orden' => 10],
            ['codigo' => 'anios', 'nombre' => 'Años Académicos', 'ruta' => 'admin.anios.index', 'icono' => 'fa-calendar-alt', 'orden' => 11],
            
            // CONFIGURACIÓN ACADÉMICA
            ['codigo' => 'configuracion-academica', 'nombre' => 'Conf. Académica', 'ruta' => 'admin.configuracion-academica.index', 'icono' => 'fa-sliders-h', 'orden' => 20],
            ['codigo' => 'aulas', 'nombre' => 'Aulas', 'ruta' => 'admin.aulas.index', 'icono' => 'fa-door-open', 'orden' => 21],
            ['codigo' => 'cursos', 'nombre' => 'Cursos', 'ruta' => 'admin.cursos-jerarquico.index', 'icono' => 'fa-book', 'orden' => 22],
            ['codigo' => 'periodos', 'nombre' => 'Periodos', 'ruta' => 'admin.periodos.index', 'icono' => 'fa-calendar-week', 'orden' => 23],
            
            // GESTIÓN ESCOLAR
            ['codigo' => 'alumnos', 'nombre' => 'Alumnos', 'ruta' => 'admin.alumnos.index', 'icono' => 'fa-user-graduate', 'orden' => 30],
            ['codigo' => 'apoderados', 'nombre' => 'Apoderados', 'ruta' => 'admin.apoderados.index', 'icono' => 'fa-users', 'orden' => 31],
            ['codigo' => 'matriculas', 'nombre' => 'Matrículas', 'ruta' => 'admin.matriculas.index', 'icono' => 'fa-address-card', 'orden' => 32],
            ['codigo' => 'carga-horaria', 'nombre' => 'Carga Horaria', 'ruta' => 'admin.carga-horaria.index', 'icono' => 'fa-clock', 'orden' => 33],
            
            // EVALUACIONES
            ['codigo' => 'notas', 'nombre' => 'Registro de Notas', 'ruta' => 'admin.notas.index', 'icono' => 'fa-edit', 'orden' => 40],
            ['codigo' => 'competencias-transversales', 'nombre' => 'Competencias Transversales', 'ruta' => 'admin.registro-competencias-transversales.index', 'icono' => 'fa-exchange-alt', 'orden' => 41],
            ['codigo' => 'apreciaciones', 'nombre' => 'Apreciaciones', 'ruta' => 'admin.apreciaciones.index', 'icono' => 'fa-comment-dots', 'orden' => 42],
            ['codigo' => 'evaluaciones-padre', 'nombre' => 'Evaluación del Padre', 'ruta' => 'admin.registro-evaluaciones.index', 'icono' => 'fa-clipboard-list', 'orden' => 43],
            ['codigo' => 'otras-evaluaciones', 'nombre' => 'Otras Evaluaciones', 'ruta' => 'admin.registro-otras-evaluaciones.index', 'icono' => 'fa-tasks', 'orden' => 44],
            ['codigo' => 'inasistencias', 'nombre' => 'Inasistencias', 'ruta' => 'admin.registro-asistencias.index', 'icono' => 'fa-calendar-check', 'orden' => 45],
            
            // REPORTES
            ['codigo' => 'libretas', 'nombre' => 'Libretas', 'ruta' => 'admin.libretas.index', 'icono' => 'fa-print', 'orden' => 50],
            ['codigo' => 'configuracion-notas', 'nombre' => 'Config. Notas', 'ruta' => 'admin.configuracion-notas.index', 'icono' => 'fa-tags', 'orden' => 51],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};