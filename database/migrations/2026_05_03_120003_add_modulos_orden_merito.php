<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('modulos')) {
            $now = now();

            \Illuminate\Support\Facades\DB::table('modulos')->updateOrInsert(
                ['codigo' => 'tipos-orden-merito-jerarquico'],
                [
                    'nombre' => 'Configurar Orden de Mérito',
                    'ruta' => 'admin.tipos-orden-merito-jerarquico.index',
                    'icono' => 'fa-trophy',
                    'orden' => 33,
                    'padre_id' => null,
                    'activo' => 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            \Illuminate\Support\Facades\DB::table('modulos')->updateOrInsert(
                ['codigo' => 'registro-orden-meritos'],
                [
                    'nombre' => 'Registro de Orden de Mérito',
                    'ruta' => 'admin.registro-orden-meritos.index',
                    'icono' => 'fa-award',
                    'orden' => 62,
                    'padre_id' => null,
                    'activo' => 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            \Illuminate\Support\Facades\DB::table('modulos')->updateOrInsert(
                ['codigo' => 'registro-orden-meritos-habilitar'],
                [
                    'nombre' => 'Habilitar Orden de Mérito',
                    'ruta' => null,
                    'icono' => 'fa-lock-open',
                    'orden' => 63,
                    'padre_id' => null,
                    'activo' => 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $adminRole = \Illuminate\Support\Facades\DB::table('roles')->where('nombre', 'admin')->first();
            if ($adminRole && \Illuminate\Support\Facades\Schema::hasTable('rol_modulo')) {
                $moduloIds = \Illuminate\Support\Facades\DB::table('modulos')
                    ->whereIn('codigo', ['tipos-orden-merito-jerarquico', 'registro-orden-meritos', 'registro-orden-meritos-habilitar'])
                    ->pluck('id');

                foreach ($moduloIds as $moduloId) {
                    \Illuminate\Support\Facades\DB::table('rol_modulo')->updateOrInsert(
                        ['rol_id' => $adminRole->id, 'modulo_id' => $moduloId],
                        ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            }

            $docenteRole = \Illuminate\Support\Facades\DB::table('roles')->where('nombre', 'docente')->first();
            if ($docenteRole && \Illuminate\Support\Facades\Schema::hasTable('rol_modulo')) {
                $registroModuloId = \Illuminate\Support\Facades\DB::table('modulos')
                    ->where('codigo', 'registro-orden-meritos')
                    ->value('id');

                if ($registroModuloId) {
                    \Illuminate\Support\Facades\DB::table('rol_modulo')->updateOrInsert(
                        ['rol_id' => $docenteRole->id, 'modulo_id' => $registroModuloId],
                        ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('modulos_registro')) {
            $now = now();
            \Illuminate\Support\Facades\DB::table('modulos_registro')->updateOrInsert(
                ['codigo' => 'orden_merito'],
                [
                    'nombre' => 'Orden de Mérito',
                    'descripcion' => 'Registro de orden de mérito por aula y periodo',
                    'ruta' => 'registro-orden-meritos.index',
                    'activo' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('modulos')) {
            $codigos = ['tipos-orden-merito-jerarquico', 'registro-orden-meritos', 'registro-orden-meritos-habilitar'];
            $moduloIds = \Illuminate\Support\Facades\DB::table('modulos')->whereIn('codigo', $codigos)->pluck('id');

            if (\Illuminate\Support\Facades\Schema::hasTable('rol_modulo') && $moduloIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('rol_modulo')->whereIn('modulo_id', $moduloIds)->delete();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('usuario_modulo_extra') && $moduloIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('usuario_modulo_extra')->whereIn('modulo_id', $moduloIds)->delete();
            }

            \Illuminate\Support\Facades\DB::table('modulos')->whereIn('codigo', $codigos)->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('modulos_registro')) {
            \Illuminate\Support\Facades\DB::table('modulos_registro')->where('codigo', 'orden_merito')->delete();
        }
    }
};
