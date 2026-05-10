<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->index(['periodo_id', 'tipo_evaluacion', 'matricula_id'], 'idx_notas_periodo_tipo_matricula');
            $table->index(['periodo_id', 'tipo_evaluacion', 'competencia_id'], 'idx_notas_periodo_tipo_competencia');
        });

        Schema::table('carga_horaria', function (Blueprint $table) {
            $table->index(['aula_id', 'curso_id', 'deleted_at'], 'idx_carga_aula_curso_deleted');
        });

        Schema::table('matriculas', function (Blueprint $table) {
            $table->index(['aula_id', 'alumno_id'], 'idx_matriculas_aula_alumno');
        });

        Schema::table('competencias', function (Blueprint $table) {
            $table->index(['curso_id', 'activo'], 'idx_competencias_curso_activo');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->index('estado', 'idx_alumnos_estado');
        });
    }

    public function down(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->dropIndex('idx_notas_periodo_tipo_matricula');
            $table->dropIndex('idx_notas_periodo_tipo_competencia');
        });

        Schema::table('carga_horaria', function (Blueprint $table) {
            $table->dropIndex('idx_carga_aula_curso_deleted');
        });

        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropIndex('idx_matriculas_aula_alumno');
        });

        Schema::table('competencias', function (Blueprint $table) {
            $table->dropIndex('idx_competencias_curso_activo');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropIndex('idx_alumnos_estado');
        });
    }
};
