<?php
// database/seeders/ConfiguracionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        Configuracion::setValor('apreciaciones_caracteres_max', 255, 'Cantidad máxima de caracteres permitidos en las apreciaciones', 'numero');
        Configuracion::setValor('apreciaciones_roles_permitidos', ['docente', 'tutor'], 'Roles que pueden registrar apreciaciones', 'array');
    }
}