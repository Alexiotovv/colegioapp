<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Buscar y eliminar la FK existente sobre tipo_orden_merito_id (si existe)
        $fk = DB::select("SELECT CONSTRAINT_NAME as name FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='registro_orden_meritos' AND COLUMN_NAME='tipo_orden_merito_id' AND REFERENCED_TABLE_NAME='tipos_orden_merito' LIMIT 1");
        if (!empty($fk)) {
            $name = $fk[0]->name;
            DB::statement("ALTER TABLE `registro_orden_meritos` DROP FOREIGN KEY `{$name}`;");
        }

        // Modificar columna a nullable
        DB::statement("ALTER TABLE `registro_orden_meritos` MODIFY `tipo_orden_merito_id` BIGINT UNSIGNED NULL;");

        // Re-crear FK apuntando a tipos_orden_merito con ON DELETE SET NULL
        DB::statement("ALTER TABLE `registro_orden_meritos` ADD CONSTRAINT `fk_registro_orden_tipo` FOREIGN KEY (`tipo_orden_merito_id`) REFERENCES `tipos_orden_merito`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;");
    }

    public function down(): void
    {
        // Eliminar FK creada
        $fk = DB::select("SELECT CONSTRAINT_NAME as name FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='registro_orden_meritos' AND COLUMN_NAME='tipo_orden_merito_id' AND REFERENCED_TABLE_NAME='tipos_orden_merito' LIMIT 1");
        if (!empty($fk)) {
            $name = $fk[0]->name;
            DB::statement("ALTER TABLE `registro_orden_meritos` DROP FOREIGN KEY `{$name}`;");
        }

        // Volver columna a NOT NULL (siempre que no existan valores nulos en datos)
        DB::statement("ALTER TABLE `registro_orden_meritos` MODIFY `tipo_orden_merito_id` BIGINT UNSIGNED NOT NULL;");

        // Re-crear FK con ON DELETE CASCADE (comportamiento anterior asumido)
        DB::statement("ALTER TABLE `registro_orden_meritos` ADD CONSTRAINT `fk_registro_orden_tipo` FOREIGN KEY (`tipo_orden_merito_id`) REFERENCES `tipos_orden_merito`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
    }
};
