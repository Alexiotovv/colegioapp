<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('modulos')) {
            $now = now();

            \Illuminate\Support\Facades\DB::table('modulos')->updateOrInsert(
                ['codigo' => 'avance-registro-notas'],
                [
                    'nombre' => 'Registro de Avance de Notas',
                    'ruta' => 'admin.avance-registro-notas.index',
                    'icono' => 'fa-book-open',
                    'orden' => 50,
                    'padre_id' => null,
                    'activo' => 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            \Illuminate\Support\Facades\DB::table('modulos')->updateOrInsert(
                ['codigo' => 'avance-registro-notas-habilitar'],
                [
                    'nombre' => 'Habilitar Avance de Notas',
                    'ruta' => null,
                    'icono' => 'fa-lock-open',
                    'orden' => 51,
                    'padre_id' => null,
                    'activo' => 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            if (\Illuminate\Support\Facades\Schema::hasTable('rol_modulo')) {
                $mainModuloId = \Illuminate\Support\Facades\DB::table('modulos')->where('codigo', 'avance-registro-notas')->value('id');
                $toggleModuloId = \Illuminate\Support\Facades\DB::table('modulos')->where('codigo', 'avance-registro-notas-habilitar')->value('id');

                foreach (['admin', 'docente', 'director', 'apoderado'] as $roleName) {
                    $roleId = \Illuminate\Support\Facades\DB::table('roles')->where('nombre', $roleName)->value('id');
                    if ($roleId && $mainModuloId) {
                        \Illuminate\Support\Facades\DB::table('rol_modulo')->updateOrInsert(
                            ['rol_id' => $roleId, 'modulo_id' => $mainModuloId],
                            ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
                        );
                    }
                }

                $adminRoleId = \Illuminate\Support\Facades\DB::table('roles')->where('nombre', 'admin')->value('id');
                if ($adminRoleId && $toggleModuloId) {
                    \Illuminate\Support\Facades\DB::table('rol_modulo')->updateOrInsert(
                        ['rol_id' => $adminRoleId, 'modulo_id' => $toggleModuloId],
                        ['activo' => true, 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('modulos_registro')) {
            $now = now();

            \Illuminate\Support\Facades\DB::table('modulos_registro')->updateOrInsert(
                ['codigo' => 'avance-registro-notas'],
                [
                    'nombre' => 'Registro de Avance de Notas',
                    'descripcion' => 'Registro paralelo de avance de notas de libreta',
                    'ruta' => 'admin.avance-registro-notas.index',
                    'activo' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            if (\Illuminate\Support\Facades\Schema::hasTable('modulo_tipos_notas')) {
                $sourceModuloId = \Illuminate\Support\Facades\DB::table('modulos_registro')->where('codigo', 'notas')->value('id');
                $targetModuloId = \Illuminate\Support\Facades\DB::table('modulos_registro')->where('codigo', 'avance-registro-notas')->value('id');

                if ($sourceModuloId && $targetModuloId) {
                    $tipos = \Illuminate\Support\Facades\DB::table('modulo_tipos_notas')
                        ->where('modulo_id', $sourceModuloId)
                        ->get();

                    foreach ($tipos as $tipo) {
                        \Illuminate\Support\Facades\DB::table('modulo_tipos_notas')->updateOrInsert(
                            ['modulo_id' => $targetModuloId, 'tipo_nota_id' => $tipo->tipo_nota_id],
                            [
                                'activo' => $tipo->activo,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]
                        );
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('modulos')) {
            $codigos = ['avance-registro-notas', 'avance-registro-notas-habilitar'];
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
            $targetModuloId = \Illuminate\Support\Facades\DB::table('modulos_registro')->where('codigo', 'avance-registro-notas')->value('id');
            if ($targetModuloId && \Illuminate\Support\Facades\Schema::hasTable('modulo_tipos_notas')) {
                \Illuminate\Support\Facades\DB::table('modulo_tipos_notas')->where('modulo_id', $targetModuloId)->delete();
            }

            \Illuminate\Support\Facades\DB::table('modulos_registro')->where('codigo', 'avance-registro-notas')->delete();
        }
    }
};