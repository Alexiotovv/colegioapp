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
            // ==================== MÓDULOS PRINCIPALES ====================
            ['codigo' => 'dashboard', 'nombre' => 'Dashboard', 'ruta' => 'dashboard', 'icono' => 'fa-home', 'orden' => 1, 'padre_id' => null, 'activo' => 1],
            
            // ==================== ADMINISTRACIÓN ====================
            ['codigo' => 'users', 'nombre' => 'Usuarios', 'ruta' => 'admin.users.index', 'icono' => 'fa-users', 'orden' => 10, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'anios', 'nombre' => 'Años Académicos', 'ruta' => 'admin.anios.index', 'icono' => 'fa-calendar-alt', 'orden' => 11, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'periodos', 'nombre' => 'Periodos', 'ruta' => 'admin.periodos.index', 'icono' => 'fa-calendar-week', 'orden' => 12, 'padre_id' => null, 'activo' => 1],
            
            // ==================== CONFIGURACIÓN ACADÉMICA ====================
            ['codigo' => 'configuracion-academica', 'nombre' => 'Configuración Académica', 'ruta' => 'admin.configuracion-academica.index', 'icono' => 'fa-sliders-h', 'orden' => 20, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'aulas', 'nombre' => 'Aulas', 'ruta' => 'admin.aulas.index', 'icono' => 'fa-door-open', 'orden' => 24, 'padre_id' => null, 'activo' => 1],
            
            // ==================== CONFIGURACIÓN JERÁRQUICA (CRUD en una sola vista) ====================
            ['codigo' => 'cursos-jerarquico', 'nombre' => 'Configurar Cursos (Jerárquico)', 'ruta' => 'admin.cursos-jerarquico.index', 'icono' => 'fa-sitemap', 'orden' => 28, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'evaluaciones-jerarquico', 'nombre' => 'Configurar Evaluaciones', 'ruta' => 'admin.evaluaciones-jerarquico.index', 'icono' => 'fa-list-alt', 'orden' => 29, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'tipos-inasistencia-jerarquico', 'nombre' => 'Configurar Tipos Inasistencia', 'ruta' => 'admin.tipos-inasistencia-jerarquico.index', 'icono' => 'fa-calendar-times', 'orden' => 30, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'tipos-otras-evaluaciones-jerarquico', 'nombre' => 'Configurar Otras Evaluaciones', 'ruta' => 'admin.tipos-otras-evaluaciones-jerarquico.index', 'icono' => 'fa-tasks', 'orden' => 31, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'competencias-transversales-jerarquico', 'nombre' => 'Configurar Competencias Transversales', 'ruta' => 'admin.competencias-transversales-jerarquico.index', 'icono' => 'fa-exchange-alt', 'orden' => 32, 'padre_id' => null, 'activo' => 1],
            
            // ==================== GESTIÓN ESCOLAR ====================
            ['codigo' => 'alumnos', 'nombre' => 'Alumnos', 'ruta' => 'admin.alumnos.index', 'icono' => 'fa-user-graduate', 'orden' => 40, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'apoderados', 'nombre' => 'Apoderados', 'ruta' => 'admin.apoderados.index', 'icono' => 'fa-users', 'orden' => 41, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'matriculas', 'nombre' => 'Matrículas', 'ruta' => 'admin.matriculas.index', 'icono' => 'fa-address-card', 'orden' => 42, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'carga-horaria', 'nombre' => 'Carga Horaria', 'ruta' => 'admin.carga-horaria.index', 'icono' => 'fa-clock', 'orden' => 43, 'padre_id' => null, 'activo' => 1],
            
            // ==================== REGISTRO DE EVALUACIONES ====================
            ['codigo' => 'notas', 'nombre' => 'Registro de Notas', 'ruta' => 'admin.notas.index', 'icono' => 'fa-edit', 'orden' => 50, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'notas-habilitar', 'nombre' => 'Habilitar Periodo Notas', 'ruta' => null, 'icono' => 'fa-lock-open', 'orden' => 51, 'padre_id' => null, 'activo' => 1],
            
            ['codigo' => 'apreciaciones', 'nombre' => 'Apreciaciones', 'ruta' => 'admin.apreciaciones.index', 'icono' => 'fa-comment-dots', 'orden' => 52, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'apreciaciones-habilitar', 'nombre' => 'Habilitar Periodo Apreciaciones', 'ruta' => null, 'icono' => 'fa-lock-open', 'orden' => 53, 'padre_id' => null, 'activo' => 1],
            
            ['codigo' => 'registro-evaluaciones', 'nombre' => 'Evaluación del Padre', 'ruta' => 'admin.registro-evaluaciones.index', 'icono' => 'fa-clipboard-list', 'orden' => 54, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'registro-evaluaciones-habilitar', 'nombre' => 'Habilitar Evaluación Padre', 'ruta' => null, 'icono' => 'fa-lock-open', 'orden' => 55, 'padre_id' => null, 'activo' => 1],
            
            ['codigo' => 'registro-asistencias', 'nombre' => 'Registro de Inasistencias', 'ruta' => 'admin.registro-asistencias.index', 'icono' => 'fa-calendar-check', 'orden' => 56, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'registro-asistencias-habilitar', 'nombre' => 'Habilitar Registro Inasistencias', 'ruta' => null, 'icono' => 'fa-lock-open', 'orden' => 57, 'padre_id' => null, 'activo' => 1],
            
            ['codigo' => 'registro-otras-evaluaciones', 'nombre' => 'Otras Evaluaciones', 'ruta' => 'admin.registro-otras-evaluaciones.index', 'icono' => 'fa-tasks', 'orden' => 58, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'registro-otras-evaluaciones-habilitar', 'nombre' => 'Habilitar Otras Evaluaciones', 'ruta' => null, 'icono' => 'fa-lock-open', 'orden' => 59, 'padre_id' => null, 'activo' => 1],
            
            ['codigo' => 'registro-competencias-transversales', 'nombre' => 'Competencias Transversales', 'ruta' => 'admin.registro-competencias-transversales.index', 'icono' => 'fa-exchange-alt', 'orden' => 60, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'registro-competencias-transversales-habilitar', 'nombre' => 'Habilitar Competencias Transversales', 'ruta' => null, 'icono' => 'fa-lock-open', 'orden' => 61, 'padre_id' => null, 'activo' => 1],
            
            // ==================== REPORTES ====================
            ['codigo' => 'libretas', 'nombre' => 'Libretas', 'ruta' => 'admin.libretas.index', 'icono' => 'fa-print', 'orden' => 70, 'padre_id' => null, 'activo' => 1],
            
            // ==================== CONFIGURACIÓN DEL SISTEMA ====================
            ['codigo' => 'configuracion-sistema', 'nombre' => 'Configuración del Sistema', 'ruta' => 'admin.configuracion.index', 'icono' => 'fa-cog', 'orden' => 80, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'configuracion-notas', 'nombre' => 'Configuración de Notas', 'ruta' => 'admin.configuracion-notas.index', 'icono' => 'fa-tags', 'orden' => 81, 'padre_id' => null, 'activo' => 1],
            
            // ==================== GESTIÓN DE PERMISOS ====================
            ['codigo' => 'modulos-gestion', 'nombre' => 'Gestión de Módulos', 'ruta' => 'admin.modulos.index', 'icono' => 'fa-cubes', 'orden' => 90, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'permisos-roles', 'nombre' => 'Permisos por Rol', 'ruta' => 'admin.permisos.asignar-roles', 'icono' => 'fa-tag', 'orden' => 91, 'padre_id' => null, 'activo' => 1],
            ['codigo' => 'permisos-usuarios', 'nombre' => 'Permisos por Usuario', 'ruta' => 'admin.permisos.asignar-usuarios', 'icono' => 'fa-user-plus', 'orden' => 92, 'padre_id' => null, 'activo' => 1],
        ]);

        
    }

    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};