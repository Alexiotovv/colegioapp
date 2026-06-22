<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('modulos')) {
            return;
        }

        $now = now();

        DB::table('modulos')->updateOrInsert(
            ['codigo' => 'avance-libretas'],
            [
                'nombre' => 'Exportar Avance Libretas',
                'ruta' => 'admin.avance-libretas.index',
                'icono' => 'fa-print',
                'orden' => 73,
                'padre_id' => null,
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('modulos')->updateOrInsert(
            ['codigo' => 'avance-exportar-por-alumno'],
            [
                'nombre' => 'Exportar Avance Libreta por Alumno',
                'ruta' => 'admin.avance-libretas.exportar-por-alumno.index',
                'icono' => 'fa-user-graduate',
                'orden' => 74,
                'padre_id' => null,
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        if (!Schema::hasTable('rol_modulo')) {
            return;
        }

        $avanceLibretasId = DB::table('modulos')->where('codigo', 'avance-libretas')->value('id');
        $avanceAlumnoId = DB::table('modulos')->where('codigo', 'avance-exportar-por-alumno')->value('id');
        $baseLibretasId = DB::table('modulos')->where('codigo', 'libretas')->value('id');
        $baseAlumnoId = DB::table('modulos')->where('codigo', 'exportar-por-alumno')->value('id');

        if ($avanceLibretasId && $baseLibretasId) {
            $rolesConBase = DB::table('rol_modulo')
                ->where('modulo_id', $baseLibretasId)
                ->where('activo', true)
                ->pluck('rol_id');

            foreach ($rolesConBase as $rolId) {
                DB::table('rol_modulo')->updateOrInsert(
                    ['rol_id' => $rolId, 'modulo_id' => $avanceLibretasId],
                    ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        if ($avanceAlumnoId && $baseAlumnoId) {
            $rolesConBaseAlumno = DB::table('rol_modulo')
                ->where('modulo_id', $baseAlumnoId)
                ->where('activo', true)
                ->pluck('rol_id');

            foreach ($rolesConBaseAlumno as $rolId) {
                DB::table('rol_modulo')->updateOrInsert(
                    ['rol_id' => $rolId, 'modulo_id' => $avanceAlumnoId],
                    ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        if (!Schema::hasTable('usuario_modulo_extra')) {
            return;
        }

        if ($avanceLibretasId && $baseLibretasId) {
            $usuariosConBase = DB::table('usuario_modulo_extra')
                ->where('modulo_id', $baseLibretasId)
                ->where('activo', true)
                ->pluck('usuario_id');

            foreach ($usuariosConBase as $usuarioId) {
                DB::table('usuario_modulo_extra')->updateOrInsert(
                    ['usuario_id' => $usuarioId, 'modulo_id' => $avanceLibretasId],
                    ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        if ($avanceAlumnoId && $baseAlumnoId) {
            $usuariosConBaseAlumno = DB::table('usuario_modulo_extra')
                ->where('modulo_id', $baseAlumnoId)
                ->where('activo', true)
                ->pluck('usuario_id');

            foreach ($usuariosConBaseAlumno as $usuarioId) {
                DB::table('usuario_modulo_extra')->updateOrInsert(
                    ['usuario_id' => $usuarioId, 'modulo_id' => $avanceAlumnoId],
                    ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('modulos')) {
            return;
        }

        $ids = DB::table('modulos')
            ->whereIn('codigo', ['avance-libretas', 'avance-exportar-por-alumno'])
            ->pluck('id');

        if (Schema::hasTable('rol_modulo') && $ids->isNotEmpty()) {
            DB::table('rol_modulo')->whereIn('modulo_id', $ids)->delete();
        }

        if (Schema::hasTable('usuario_modulo_extra') && $ids->isNotEmpty()) {
            DB::table('usuario_modulo_extra')->whereIn('modulo_id', $ids)->delete();
        }

        DB::table('modulos')->whereIn('codigo', ['avance-libretas', 'avance-exportar-por-alumno'])->delete();
    }
};
