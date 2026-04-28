<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar el tipo de la columna a string para aceptar códigos configurables como 'A', 'B', etc.
        DB::statement('ALTER TABLE `reg_eval_actitudinales` MODIFY `valoracion` VARCHAR(255) NOT NULL');
    }

    public function down(): void
    {
        // Volver al enum original si se revierte la migración
        DB::statement('ALTER TABLE `reg_eval_actitudinales` MODIFY `valoracion` ENUM("SIEMPRE", "CASI SIEMPRE", "ALGUNAS VECES", "NUNCA") NOT NULL');
    }
};
