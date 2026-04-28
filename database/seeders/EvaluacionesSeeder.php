<?php
// database/seeders/EvaluacionesSeeder.php
// Para cargar evaluaciones predefinidas en desarrollo

namespace Database\Seeders;

use App\Models\Evaluacion;
use App\Models\Nivel;
use App\Models\TipoNota;
use Illuminate\Database\Seeder;

class EvaluacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener niveles
        $primaria = Nivel::where('nombre', 'Primaria')->first();
        $secundaria = Nivel::where('nombre', 'Secundaria')->first();

        if (!$primaria || !$secundaria) {
            $this->command->warn('Niveles no encontrados. Ejecuta NivelesSeeder primero.');
            return;
        }

        // Obtener tipos de notas
        $tiposNotas = TipoNota::activo()->get();

        // ==================== EVALUACIONES PRIMARIA ====================

        // 1. Evaluación de Comportamiento (LITERAL)
        $evaluacionComportamiento = Evaluacion::create([
            'nombre' => 'Evaluación de Comportamiento',
            'etiqueta_libreta' => 'Comportamiento',
            'descripcion' => 'Evaluación del comportamiento y disciplina del alumno',
            'nivel_id' => $primaria->id,
            'tipo_escala' => 'LITERAL',
            'opciones_literales' => json_encode(['AD', 'A', 'B', 'C']),
            'visible_primaria' => true,
            'visible_secundaria' => false,
            'visible_apoderado' => true,
            'orden' => 1,
            'activo' => true,
            'requerido' => true,  // 🔴 Requerido
            'permitir_comentarios' => true,
        ]);

        // Vincular tipos de notas
        if ($tiposNotas->count() > 0) {
            $evaluacionComportamiento->tiposNotas()->attach(
                $tiposNotas->slice(0, 4)->pluck('id')->toArray(),
                ['orden' => 0, 'activo' => true]
            );
        }

        // 2. Evaluación de Participación en Clase (LITERAL)
        $evaluacionParticipacion = Evaluacion::create([
            'nombre' => 'Participación en Clase',
            'etiqueta_libreta' => 'Participación',
            'descripcion' => 'Evaluación de la participación activa en clase',
            'nivel_id' => $primaria->id,
            'tipo_escala' => 'LITERAL',
            'opciones_literales' => json_encode(['SIEMPRE', 'A MENUDO', 'A VECES', 'NUNCA']),
            'visible_primaria' => true,
            'visible_apoderado' => true,
            'orden' => 2,
            'activo' => true,
            'requerido' => true,  // 🔴 Requerido
        ]);

        // 3. Prueba Mensual (NUMÉRICO)
        $evaluacionPruebaMonthly = Evaluacion::create([
            'nombre' => 'Prueba Mensual',
            'etiqueta_libreta' => 'Prueba',
            'descripcion' => 'Prueba escrita mensual de conocimientos',
            'nivel_id' => $primaria->id,
            'tipo_escala' => 'NUMERICO',
            'escala_minima' => 0,
            'escala_maxima' => 20,
            'visible_primaria' => true,
            'visible_apoderado' => true,
            'orden' => 3,
            'activo' => true,
            'requerido' => true,  // 🔴 Requerido
        ]);

        // 4. Tareas y Deberes (LITERAL)
        $evaluacionTareas = Evaluacion::create([
            'nombre' => 'Cumplimiento de Tareas',
            'etiqueta_libreta' => 'Tareas',
            'descripcion' => 'Evaluación del cumplimiento de tareas y deberes',
            'nivel_id' => $primaria->id,
            'tipo_escala' => 'LITERAL',
            'opciones_literales' => json_encode(['EXCELENTE', 'BUENO', 'REGULAR', 'MALO']),
            'visible_primaria' => true,
            'visible_apoderado' => true,
            'orden' => 4,
            'activo' => true,
            'requerido' => false,
            'permitir_comentarios' => true,
        ]);

        // ==================== EVALUACIONES SECUNDARIA ====================

        // 1. Evaluación Formativa (LITERAL)
        $evaluacionFormativa = Evaluacion::create([
            'nombre' => 'Evaluación Formativa',
            'etiqueta_libreta' => 'Formativa',
            'descripcion' => 'Evaluación del aprendizaje en proceso',
            'nivel_id' => $secundaria->id,
            'tipo_escala' => 'LITERAL',
            'opciones_literales' => json_encode(['AD', 'A', 'B', 'C']),
            'visible_primaria' => false,
            'visible_secundaria' => true,
            'visible_apoderado' => true,
            'orden' => 1,
            'activo' => true,
            'requerido' => true,  // 🔴 Requerido
            'permitir_comentarios' => true,
        ]);

        // 2. Examen Trimestral (NUMÉRICO)
        $evaluacionExamen = Evaluacion::create([
            'nombre' => 'Examen Trimestral',
            'etiqueta_libreta' => 'Examen',
            'descripcion' => 'Examen integral por trimestre',
            'nivel_id' => $secundaria->id,
            'tipo_escala' => 'NUMERICO',
            'escala_minima' => 0,
            'escala_maxima' => 20,
            'visible_secundaria' => true,
            'visible_apoderado' => true,
            'orden' => 2,
            'activo' => true,
            'requerido' => true,  // 🔴 Requerido
        ]);

        // 3. Trabajos Prácticos (NUMÉRICO)
        $evaluacionTrabajos = Evaluacion::create([
            'nombre' => 'Trabajos Prácticos',
            'etiqueta_libreta' => 'Trabajos',
            'descripcion' => 'Evaluación de proyectos y trabajos prácticos',
            'nivel_id' => $secundaria->id,
            'tipo_escala' => 'NUMERICO',
            'escala_minima' => 0,
            'escala_maxima' => 100,
            'visible_secundaria' => true,
            'visible_apoderado' => true,
            'orden' => 3,
            'activo' => true,
            'requerido' => true,  // 🔴 Requerido
        ]);

        // 4. Asistencia (LITERAL)
        $evaluacionAsistencia = Evaluacion::create([
            'nombre' => 'Asistencia',
            'etiqueta_libreta' => 'Asistencia',
            'descripcion' => 'Registro de asistencia del estudiante',
            'nivel_id' => $secundaria->id,
            'tipo_escala' => 'LITERAL',
            'opciones_literales' => json_encode(['PRESENTE', 'JUSTIFICADO', 'FALTA']),
            'visible_secundaria' => true,
            'visible_apoderado' => true,
            'orden' => 4,
            'activo' => true,
            'requerido' => true,  // 🔴 Requerido
            'permitir_comentarios' => false,
        ]);

        // 5. Evaluación Híbrida (HIBRIDO)
        $evaluacionHibrida = Evaluacion::create([
            'nombre' => 'Evaluación Integral',
            'etiqueta_libreta' => 'Integral',
            'descripcion' => 'Evaluación que combina criterios literales y numéricos',
            'nivel_id' => $secundaria->id,
            'tipo_escala' => 'HIBRIDO',
            'opciones_literales' => json_encode(['EXCELENTE', 'BUENO', 'REGULAR']),
            'escala_minima' => 0,
            'escala_maxima' => 10,
            'visible_secundaria' => true,
            'visible_apoderado' => true,
            'orden' => 5,
            'activo' => true,
            'requerido' => false,
            'permitir_comentarios' => true,
        ]);

        // ==================== EVALUACIONES AMBOS NIVELES ====================

        // Evaluación Transversal de Valores
        $evaluacionValores = Evaluacion::create([
            'nombre' => 'Evaluación de Valores',
            'etiqueta_libreta' => 'Valores',
            'descripcion' => 'Evaluación de valores y actitudes',
            'nivel_id' => $primaria->id,
            'tipo_escala' => 'LITERAL',
            'opciones_literales' => json_encode(['AD', 'A', 'B', 'C']),
            'visible_primaria' => true,
            'visible_secundaria' => false,
            'visible_apoderado' => true,
            'orden' => 99,
            'activo' => true,
            'requerido' => false,
            'permitir_comentarios' => true,
        ]);

        $this->command->info('✅ 10 evaluaciones creadas exitosamente');
        
        // Mostrar resumen
        $this->command->line('');
        $this->command->line('<fg=cyan>📊 RESUMEN DE EVALUACIONES CREADAS</>');
        $this->command->line('─────────────────────────────────────');
        $this->command->line("<fg=yellow>Primaria:</>  " . Evaluacion::where('nivel_id', $primaria->id)->count() . " evaluaciones");
        $this->command->line("<fg=yellow>Secundaria:</> " . Evaluacion::where('nivel_id', $secundaria->id)->count() . " evaluaciones");
        $this->command->line('─────────────────────────────────────');
        $this->command->line("<fg=green>Total:</> " . Evaluacion::count() . " evaluaciones");
    }
}
